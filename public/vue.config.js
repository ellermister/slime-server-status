module.exports = {
    devServer: {
      proxy: {
        '/api': {
          target: process.env.VUE_APP_HTTP_URL,
          changeOrigin: true,
          ws: true,
          pathRewrite: {
            '^/api': '/api'
          }
        }
      }
    }
  }
  