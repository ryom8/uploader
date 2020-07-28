var DLwindow;

$(function(){

    var upFiles = 0;
    var checks = [];
    var sendTarget = [];
    var sendFlg = true;   

    var obj = $('#uploader');
    obj.on('dragenter', function (e){
        e.stopPropagation();
        e.preventDefault();
        $(this).css('border', '2px solid #0B85A1');
    });

    obj.on('dragover', function (e){
        e.stopPropagation();
        e.preventDefault();
    });

    obj.on('drop', function (e){    
        $(this).css('border', '2px dotted #0B85A1');
        e.preventDefault();
        var files = e.originalEvent.dataTransfer.files;
    
        //We need to send dropped files to Server
        upFiles++;
        handleFileUpload(files,obj,upFiles);
    });

    $(document).on('dragenter', function (e){
        e.stopPropagation();
        e.preventDefault();
    });

    $(document).on('dragover', function (e){
        e.stopPropagation();
        e.preventDefault();
        obj.css('border', '2px dotted #0B85A1');
    });

    $(document).on('drop', function (e){
        e.stopPropagation();
        e.preventDefault();
    });

    $('#send').prop("disabled",true);

    $(document).on('change','.sendFile',function(){
//    $('.sendFile').change(function(){
        sendKeyCkeck(sendFlg);
    });

    $(document).on('click','#send',function(){
        checks.length=0;

        $('input:checkbox:checked').each(function(){
            checks.push($(this).val());            
        });

        $('.nameForm').each(function(){
            sendTarget.push($(this).val());            
        });

        var sendFiles = { 'send':checks, 'target':sendTarget };

        $.ajax({
            type:'POST',
            url:'sendfile.php',
            data:sendFiles,
            success:function(data){
                alert(data);
            }
        });

        $('#send').prop("disabled",true);
        sendFlg = false;

    });

    $('#l_new').click(function(){
        window.open('register.php','_blank','width=400,height=400,toolbar=0,location=0,menubar=0,scrollbars=0,resizable=0');
    });

    $('#l_login').click(function(){
        window.open('login.php','_blank','width=400,height=400,toolbar=0,location=0,menubar=0,scrollbars=0,resizable=0');
    });

    $('#l_logout').click(function(){
        window.open('logout.php','_blank','width=400,height=400,toolbar=0,location=0,menubar=0,scrollbars=0,resizable=0');
    });

    $('#l_howto').click(function(){
        window.open('info.html','_blank','width=450,height=600,toolbar=0,location=0,menubar=0,scrollbars=0,resizable=0');
    });

    $('#l_change').click(function(){
        window.open('change.php','_blank','width=600,height=700,toolbar=0,location=0,menubar=0,scrollbars=0,resizable=0');
    });

    $(document).on('click','.DLown',function(){
        var DLtarget = $(this).attr('id');
        var DLname = $(this).attr('value');
        downloadFile(DLtarget,DLname,false);
    });

    $(document).on('click','.DLother',function(){
        var DLtarget = $(this).attr('id');
        var DLname = $(this).attr('value');
        downloadFile(DLtarget,DLname,true);
    });

    $(document).on('click','.DLkey',function(){
        var DLtarget = $(this).attr('id');
        downloadFile(DLtarget,'DLkey',false);
    });

    $(document).on('click','.delete',function(){
        var delFile = $(this).attr('id');
        var delTarget = $(this).attr('value');
        var dele = { 'target':delFile, 'timestamp':delTarget };
        if(confirm('以下のファイルを削除します。\n本当によろしいですか？\n\n削除ファイル名：' + delFile + '\n※キーを使ったダウンロードも出来なくなります')){
            $.ajax({
                url:'delete.php',
                type:'POST',
                data:dele,
                success:function(data){
                    alert(data);
                }
            });
        }
    });

});

// ファイル・キーのダウンロード用
function downloadFile(filename,name,reduce){
    $('#DLfile').remove();
    if((DLwindow) && (!DLwindow.closed)){
        DLwindow.close();
    }
    DLwindow = window.open('about:blank','_blank','width=0,height=0');
    
    var html = '<form method="post" action="download.php" id="DLfile" style="display: none;">';
    html += '<input type="hidden" name="file" value="' + filename + '">';
    html += '<input type="hidden" name="name" value="' + name + '">';
    html += '<input type="hidden" name="flg" value="' + reduce + '">';
    html += '</form>';
    $('body').append(html);
    $('#DLfile').submit();
    $('#DLfile').remove();

    DLwindow.close();
}

function sendKeyCkeck(flg){
    var len = $('input:checkbox:checked').length;
    if(len >= 1 && flg == true){
        $('#send').prop("disabled",false);
    }else{
        $('#send').prop("disabled",true);
    }
}

function handleFileUpload(files,obj,num){
   for (var i = 0; i < files.length; i++){
        var fd = new FormData();
        fd.append('file', files[i]);
        fd.append('num', num);
  
        var status = new createStatusbar(obj,num); //Using this we can set progress.
        status.setFileNameSize(files[i].name,files[i].size);
        sendFileToServer(fd,status,num);  
   }
}

function sendFileToServer(formData,status,ids)
{
    var uploadURL ="upload.php";
    ids = '#updata' + ids;
    var extraData ={}; //Extra Data.
    var jqXHR=$.ajax({
            xhr: function() {
            var xhrobj = $.ajaxSettings.xhr();
            if (xhrobj.upload) {
                    xhrobj.upload.addEventListener('progress', function(event) {
                        var percent = 0;
                        var position = event.loaded || event.position;
                        var total = event.total;
                        if (event.lengthComputable) {
                            percent = Math.ceil(position / total * 100);
                        }
                        //Set progress
                        status.setProgress(percent);
                    }, false);
                }
            return xhrobj;
        },
        url: uploadURL,
        type: "POST",
        contentType:false,
        processData: false,
        cache: false,
        data: formData,
        success: function(data){
            status.setProgress(100);
  
             $("#status1").append(data + '<br>');

        }
    }); 
  
    status.setAbort(jqXHR);
}

var rowCount=0;
function createStatusbar(obj,num){
    num = 'updata' + num;
     rowCount++;
     var row="odd";
     if(rowCount %2 ==0) row ="even";
     this.statusbar = $("<div class='statusbar "+row+"'></div>");
     this.filename = $("<div id="+num+" class='filename'></div>").appendTo(this.statusbar);
     this.size = $("<div class='filesize'></div>").appendTo(this.statusbar);
     this.progressBar = $("<div class='progressBar'><div></div></div>").appendTo(this.statusbar);
     this.abort = $("<div class='abort'>中止</div>").appendTo(this.statusbar);
     obj.after(this.statusbar);
//     alert(num);
 
    this.setFileNameSize = function(name,size){
        var sizeStr="";
        var sizeKB = size/1024;
        if(parseInt(sizeKB) > 1024){
            var sizeMB = sizeKB/1024;
            sizeStr = sizeMB.toFixed(2)+" MB";
        }else{
            sizeStr = sizeKB.toFixed(2)+" KB";
        }
 
        this.filename.html(name);
        this.size.html(sizeStr);
    }

    this.setProgress = function(progress){       
        var progressBarWidth =progress*this.progressBar.width()/ 100;  
        this.progressBar.find('div').animate({ width: progressBarWidth }, 10).html(progress + "% ");
        if(parseInt(progress) >= 100){
            this.abort.hide();
        }
    }

    this.setAbort = function(jqxhr){
        var sb = this.statusbar;
        this.abort.click(function(){
            jqxhr.abort();
            sb.hide();
        });
    }
}
