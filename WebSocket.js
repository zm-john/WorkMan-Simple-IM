ws = new WebSocket('ws://192.168.33.10:2345');
ws.onmessage=function(e){
    data = eval( "(" + e.data + ")");
    if (data.type == 2) { // 列表消息
        var length = data.message.list.length;
        console.log('在线用户ID：');
        for (var i=0; i < length; i++) {
            console.log(data.message.list[i]);
        }
    }
    else  if (data.type == 1) { // 普通消息
        console.log(data.from + ":" + data.message);
    }
    else if (data.type == 3) { // 系统通知
        console.log(data.message);
    }
};