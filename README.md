<p align='left'><img src="slime.png" align='left'></p>


## Slime server status

（In development）



### server

```bash
# 创建 docker 网络
docker network create b1 --subnet 172.18.0.0/24

# 创建 redis 数据库目录
mkdir /mnt/data/redis/conf -p
echo 'requirepass "gY$4pMHnfCu3zH@@"' > /mnt/data/redis/conf/redis.conf

# 启动 redis 容器
docker run --name redis \
-v /mnt/data/redis:/data \
-v /mnt/data/redis/conf:/usr/local/etc/redis \
-network=b1 -d redis \
redis-server /usr/local/etc/redis/redis.conf --appendonly yes

# 启动 slime-status 服务端
docker run -it --rm  -v $(pwd):/www hyperf/hyperf:8.0-alpine-v3.12-swoole bash -c 'cd /www && composer install --ignore-platform-reqs'

docker run --name slime-status \
-v /mnt/web/slime-server-status:/www  \
--network=b1 \
-p 80:9501 \
-d hyperf/hyperf:8.0-alpine-v3.12-swoole \
php /www/bin/hyperf.php start

```


其中`gY$4pMHnfCu3zH@@` 为你的 redis 密码

设置管理端登录密码：

```bash
docker exec -it slime-status php /www/bin/hyperf.php password:set "admin888!!!"
```



启动 slime-status 服务端 nginx

```
docker run --name slime-status \
-v /mnt/web/slime-server-status:/www  \
--network=b1 \
-d hyperf/hyperf:8.0-alpine-v3.12-swoole \
php /www/bin/hyperf.php start
```



slime-status.conf

```nginx
server {
        listen       80;
        server_name default.com;
        index index.html index.htm index.php;
        root  /app/slime-server-status/public/dist;
		
        location / {
            try_files $uri $uri/ /index.html;
        }

        location ^~ /update_status {
            proxy_http_version 1.1;
            proxy_set_header Upgrade websocket;
            proxy_set_header Connection "Upgrade";

            proxy_set_header Host $http_host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

            proxy_read_timeout 60s;
            proxy_pass http://slime-status:9501/update_status;
        }


        location ^~ /api {
            proxy_set_header Host $http_host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

            proxy_pass http://slime-status:9501/api;
        }

}
```






### build html for server

```bash
docker run --rm -it \
-v $(pwd)/public:/www \
node:16.8.0 \
bash -c "cd /www && npm i && npm run build"
```



### node-client

```bash
docker run --rm -it \
-v $(pwd)/node-client:/www \
golang:1.18.3 \
bash -c "cd /www && go mod tidy && go build -ldflags="-s -w" client.go"

slime-status -addr=wss://tz.x7.pw -nid=3020c8e7-9c1a-4d7e-beeb-050da0e38d8d	 -key=sf23d2d23d322r23r23s
```

