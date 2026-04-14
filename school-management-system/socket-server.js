import http from 'node:http';
import { Server } from 'socket.io';

const port = Number(process.env.SOCKET_PORT || 3001);
const host = process.env.SOCKET_HOST || '0.0.0.0';

const server = http.createServer((req, res) => {
  if (req.url === '/health') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ ok: true }));
    return;
  }

  res.writeHead(404, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify({ ok: false, message: 'not_found' }));
});

const io = new Server(server, {
  cors: {
    origin: '*',
    methods: ['GET', 'POST'],
  },
  transports: ['websocket', 'polling'],
});

io.on('connection', (socket) => {
  socket.on('join_room', (payload = {}) => {
    const roomCode = String(payload.roomCode || '').trim().toUpperCase();
    const userName = String(payload.userName || '').trim();
    if (!roomCode || !userName) return;

    socket.join(roomCode);
    socket.data.roomCode = roomCode;
    socket.data.userName = userName;

    io.to(roomCode).emit('room_joined', { roomCode, userName, spectator: false });
    socket.to(roomCode).emit('room_presence', { userName, progress: 0, wpm: 0, accuracy: 100 });
  });

  socket.on('typing_progress', (payload = {}) => {
    const roomCode = String(payload.roomCode || socket.data.roomCode || '').trim().toUpperCase();
    const userName = String(payload.userName || socket.data.userName || '').trim();
    if (!roomCode || !userName) return;

    socket.to(roomCode).emit('typing_progress', {
      userName,
      progress: Number(payload.progress || 0),
      wpm: Number(payload.wpm || 0),
      accuracy: Number(payload.accuracy || 100),
    });
  });
});

server.listen(port, host, () => {
  // eslint-disable-next-line no-console
  console.log(`[socket] running on ${host}:${port}`);
});

