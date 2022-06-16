package main

import (
	"encoding/json"
	"flag"
	"fmt"
	"log"
	"net/url"
	"os"
	"os/signal"
	"time"

	"github.com/gorilla/websocket"

	"github.com/gin-gonic/gin"
	"github.com/shirou/gopsutil/cpu"
	"github.com/shirou/gopsutil/host"
	"github.com/shirou/gopsutil/mem"
	"github.com/shirou/gopsutil/net"
)

func GetStat() (map[string]interface{}, error) {
	timer := time.NewTimer(500 * time.Millisecond)

	res := gin.H{}
	CPU1, err := cpu.Times(true)
	if err != nil {
		return nil, err
	}
	NET1, err := net.IOCounters(true)
	if err != nil {
		return nil, err
	}
	<-timer.C
	CPU2, err := cpu.Times(true)
	if err != nil {
		return nil, err
	}
	NET2, err := net.IOCounters(true)
	if err != nil {
		return nil, err
	}
	MEM, err := mem.VirtualMemory()
	if err != nil {
		return nil, err
	}
	SWAP, err := mem.SwapMemory()
	if err != nil {
		return nil, err
	}
	res["mem"] = gin.H{
		"virtual": MEM,
		"swap":    SWAP,
	}

	single := make([]float64, len(CPU1))
	var idle, total, multi float64
	idle, total = 0, 0
	for i, c1 := range CPU1 {
		c2 := CPU2[i]
		single[i] = 1 - (c2.Idle-c1.Idle)/(c2.Total()-c1.Total())
		idle += c2.Idle - c1.Idle
		total += c2.Total() - c1.Total()
	}
	multi = 1 - idle/total
	// info, err := cpu.Info()
	// if err != nil {
	// 	return nil, err
	// }
	res["cpu"] = gin.H{
		// "info":   info,
		"multi":  multi,
		"single": single,
	}

	var in, out, in_total, out_total uint64
	in, out, in_total, out_total = 0, 0, 0, 0
	res["net"] = gin.H{
		"devices": gin.H{},
	}
	for i, x := range NET2 {
		_in := x.BytesRecv - NET1[i].BytesRecv
		_out := x.BytesSent - NET1[i].BytesSent
		res["net"].(gin.H)["devices"].(gin.H)[x.Name] = gin.H{
			"delta": gin.H{
				"in":  float64(_in) / 0.5,
				"out": float64(_out) / 0.5,
			},
			"total": gin.H{
				"in":  x.BytesRecv,
				"out": x.BytesSent,
			},
		}
		if x.Name == "lo" {
			continue
		}
		in += _in
		out += _out
		in_total += x.BytesRecv
		out_total += x.BytesSent
	}
	res["net"].(gin.H)["delta"] = gin.H{
		"in":  float64(in) / 0.5,
		"out": float64(out) / 0.5,
	}
	res["net"].(gin.H)["total"] = gin.H{
		"in":  in_total,
		"out": out_total,
	}
	host, err := host.Info()
	if err != nil {
		return nil, err
	}
	res["host"] = host

	return res, nil
}

func keepReport(authStr string, u url.URL) {
	interrupt := make(chan os.Signal, 1)
	signal.Notify(interrupt, os.Interrupt)

	c, _, err := websocket.DefaultDialer.Dial(u.String(), nil)
	if err != nil {
		log.Fatal("dial:", err)
	}
	defer c.Close()

	done := make(chan struct{})

	authed := false

	go func() {
		defer close(done)
		for {
			_, message, err := c.ReadMessage()
			//log.Printf("[debug] recv:  %s", message)
			if string(message) == "auth" {
				log.Printf("[websocket] server: request auth")
				c.WriteMessage(websocket.TextMessage, []byte(authStr))
				log.Printf("[websocket] client: send auth")
			} else if string(message) == "authed" {
				log.Printf("[websocket] server: authed\n")
				authed = true
			} else {
				if err != nil {
					log.Println("[websocket] error: ", err)
					return
				}
				log.Printf("[websocket] server: %s", message)
			}

		}
	}()

	ticker := time.NewTicker(time.Second * 1)
	defer ticker.Stop()

	for {
		select {
		case <-done:
			return
		case <-ticker.C: //  t :=
			if authed {
				var a, _ = GetStat()

				jsonStr, _ := json.Marshal(a)
				//fmt.Printf("test:%s\n", jsonStr)

				err := c.WriteMessage(websocket.TextMessage, jsonStr)
				if err != nil {
					log.Println("write:", err)
					return
				}
			}
		case <-interrupt:
			log.Println("interrupt")

			// Cleanly close the connection by sending a close message and then
			// waiting (with timeout) for the server to close the connection.
			err := c.WriteMessage(websocket.CloseMessage, websocket.FormatCloseMessage(websocket.CloseNormalClosure, ""))
			if err != nil {
				log.Println("write close:", err)
				return
			}
			select {
			case <-done:
			case <-time.After(time.Second):
			}
			return
		}
	}
}

func main() {
	//var addr = flag.String("addr", "localhost:8080", "http service address")
	var addr = flag.String("addr", "192.168.75.132:9502", "http service address")
	var nodeId = flag.String("nid", "f0181649-8dba-4066-e6c2-0319a1d61407", "node id")
	var nodeKey = flag.String("key", "abcd123456", "node key")

	flag.Parse()
	log.SetFlags(0)

	var authStr = fmt.Sprintf("auth:%s,key:%s", *nodeId, *nodeKey)

	parseUrl, _ := url.Parse(*addr)
	if parseUrl.Scheme != "ws" && parseUrl.Scheme != "wss" {
		log.Println("Only for ws protocol, error input: " + parseUrl.Scheme)
		return
	}

	u := url.URL{Scheme: parseUrl.Scheme, Host: parseUrl.Host, Path: "/update_status"}
	log.Printf("connecting to %s", u.String())

	interrupt := make(chan os.Signal, 1)
	signal.Notify(interrupt, os.Interrupt)

	for {
		keepReport(authStr, u)
		select {
		case <-interrupt:
			return
		default:
			log.Printf("wait reconnecting")
			time.Sleep(time.Second * 5)
		}
	}
}
