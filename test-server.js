const http = require('http');

const port = process.env.PORT || 8080;

const server = http.createServer((req, res) => {
  // Health check endpoint
  if (req.url === '/api/health') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({
      status: 'ok',
      message: 'Parcelvoy SMS Platform Test Server - ROOT DIRECTORY',
      timestamp: new Date().toISOString(),
      port: port,
      directory: 'ROOT'
    }));
    return;
  }

  res.writeHead(200, { 'Content-Type': 'text/html' });
  res.end(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>🎯 ROOT DIRECTORY - Parcelvoy SMS Platform Test</title>
    </head>
    <body>
      <h1>🎯 SUCCESS! Node.js is Running from ROOT Directory!</h1>
      <p>✅ Node.js server is working</p>
      <p>✅ Digital Ocean Apps deployment successful</p>
      <p>✅ Ready for Twilio SMS integration</p>
      <p><strong>Port:</strong> ${port}</p>
      <p><strong>Environment:</strong> ${process.env.NODE_ENV || 'development'}</p>
      <p><strong>Time:</strong> ${new Date().toISOString()}</p>
      <p><strong>Directory:</strong> ROOT (not /apps/platform)</p>

      <h2>🔧 Configuration Details:</h2>
      <ul>
        <li><strong>Process:</strong> Node.js (not Apache!)</li>
        <li><strong>Location:</strong> Root directory test-server.js</li>
        <li><strong>Port:</strong> ${port} (environment controlled)</li>
      </ul>

      <h2>Next Steps:</h2>
      <ul>
        <li>✅ Confirmed Node.js can run on Digital Ocean Apps</li>
        <li>Switch to full Parcelvoy application</li>
        <li>Configure Twilio integration</li>
        <li>Test SMS functionality</li>
      </ul>
    </body>
    </html>
  `);
});

server.listen(port, '0.0.0.0', () => {
  console.log(`🎯 ROOT DIRECTORY Test server running on port ${port}`);
  console.log(`✅ Environment: ${process.env.NODE_ENV || 'development'}`);
  console.log(`✅ Time: ${new Date().toISOString()}`);
  console.log(`🔧 This proves Node.js works when in root directory`);
});

// Handle graceful shutdown
process.on('SIGTERM', () => {
  console.log('📝 Received SIGTERM, shutting down gracefully');
  server.close(() => {
    console.log('✅ Server closed');
    process.exit(0);
  });
});