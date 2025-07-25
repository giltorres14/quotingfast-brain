const http = require('http');
const httpProxy = require('http-proxy');

// Create a proxy server
const proxy = httpProxy.createProxyServer({});

// Create the server
const server = http.createServer((req, res) => {
  // Proxy all requests to the frontend server
  proxy.web(req, res, {
    target: 'http://localhost:3001',
    changeOrigin: true,
  });
});

// Handle proxy errors
proxy.on('error', (err, req, res) => {
  console.error('Proxy error:', err);
  res.writeHead(500, {
    'Content-Type': 'text/plain',
  });
  res.end('Proxy error');
});

const PORT = 8000;
server.listen(PORT, () => {
  console.log(`Proxy server running on http://localhost:${PORT}`);
  console.log(`Proxying requests to http://localhost:3001`);
});