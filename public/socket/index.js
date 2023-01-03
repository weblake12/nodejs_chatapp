var express = require('express');
var app = express();
var http = require('http').Server(app);
var io = require('socket.io')(http,{
    cors: {
        origin: '*',
    },
});

var mysql = require('mysql');
var moment = require('moment');

var sockets = {};

var con = mysql.createConnection({
    host     : 'localhost',
    user     : 'root',
    password : '',
    database : 'chatapp'
});

// con.connect();

con.connect(function (error, results, fields) {
    if (error)
        throw error;
    console.log('Database connected');
});

// con.end();

io.on('connection', function (socket) {
    if (!sockets[socket.handshake.query.user_id]) {
        sockets[socket.handshake.query.user_id] = [];
    }
    sockets[socket.handshake.query.user_id].push(socket);
    socket.broadcast.emit('user_connected', socket.handshake.query.user_id);
    socket.broadcast.emit('users_connected');

    con.query(`UPDATE users SET is_online=1 where id=${socket.handshake.query.user_id}`, function (error, res) {
        if (error)
            throw error;
        console.log('user '+socket.handshake.query.user_id+' connected.');
    });

    socket.on('send_message', function (data) {
        data.group_id = (data.user_id > data.other_user_id) ? data.user_id+data.other_user_id:data.other_user_id+data.user_id;
        data.time = moment().unix();
        for (var index in sockets[data.user_id]) {
            sockets[data.user_id][index].emit('receive_message', data);
        }
        for (var index in sockets[data.other_user_id]) {
            sockets[data.other_user_id][index].emit('receive_message', data);
        }
        con.query(`INSERT INTO chats (user_id,other_user_id,message,group_id,created_at,created_by) values(${data.user_id},${data.other_user_id},"${data.message}",${data.group_id},${data.time},${data.user_id})`, function (error, res) {
            if (error)
                throw error;
            console.log('Message sent');
        });
    });

    socket.on('read_message', function (id) {
        con.query(`UPDATE chats set is_read=1 where id=${id}`, function (error, res) {
            if (error)
                throw error;
            console.log('Message read');
        });
    });

    socket.on('disconnect', function (error) {
        socket.broadcast.emit('user_disconnected', socket.handshake.query.user_id);
        for(var index in sockets[socket.handshake.query.user_id]) {
            if (socket.id == sockets[socket.handshake.query.user_id][index].id) {
                sockets[socket.handshake.query.user_id].splice(index, 1);
            }
        }
        con.query(`UPDATE users SET is_online=0 where id=${socket.handshake.query.user_id}`, function (error, res) {
            if (error)
                throw error;
            console.log('user '+socket.handshake.query.user_id+' disconnected');
        });
    });
});

const hostname = '127.0.0.1';
const port = 3000;

http.listen(port, hostname, () => {
    console.log(`Server running at http://${hostname}:${port}/`);
});
