# WorkMan-Simple-IM
Use WorkMan to build a simple IM system

## WorkMan启动方法 在项目根目录输入命令（只能在CLI下才行）
```bash
php test.php start
```
## 测试方法，将WebSocket.js的内容复制到浏览器的consloe命令执行
例：
```javascript

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

  ws.send('{"id":"John"}'); // 向服务器注册ID，将ID与当前connection绑定
  ws.send('{"id":"John","to":"xxx","message":"****"}'); // 发送消息示例
  ws.close(); // 关闭当前连接
```
