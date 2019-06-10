<html>
    <head>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    </head>
    <div>
        <h2>
            swoole异步导出实例
            <button type="button" class="btn btn-success" id="work" style="float: right">开始导出</button>
        </h2>

    </div>
    <body>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>#</th>
                    <th>操作时间</th>
                    <th>完成时间</th>
                    <th>总耗时</th>
                    <th>进度</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tr>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </body>
</html>
<script>
    var ws = new WebSocket("ws://localhost:9501");

    ws.onopen = function(evt) {
        console.log("Connection open ...");
        var arr = [];
        arr['table'] = 'test_user';
        document.getElementById('work').onclick = function(){
            ws.send(JSON.stringify(arr));
        }
    };

    ws.onmessage = function(evt) {
        console.log("Received Message: " + evt.data);
        //ws.close();
    };

    ws.onclose = function(evt) {
        console.log("Connection closed.");
    };
</script>
