var io = require('socket.io').listen(8081);

io.on('connection', function (socket) {
    socket.on('appEvent', function (data) {
        if (typeof data.__event != 'undefined') {
            var event = data.__event;
            delete data['__event'];

            socket.broadcast.emit(event, data);
        }
    });
});