//单图片上传
function upimage(id,picName,upUrl,delUrl,img){
	//上传队列模板
	var template = '<div id="\${fileID}" class="uploadify-queue-item">'
				+'<span class="delbtn">×</span>'
				+'<div class="showthumb"></div>'
				+'<span class="up_filename">\${fileName}</span>'
				+'<input type="hidden" class="thumb" name="'+picName+'" value=""/>'
				+'</div>';
	
	var up = $('#'+id).Huploadify({
		auto:true,//是否开启自动上传
		buttonText:'选择图片',//上传按钮上的文字
		fileTypeExts:'*.jpg;*.png;*.gif;*.jpeg;',//允许上传的文件类型，格式'*.jpg;*.doc'
		multi:false,//是否允许选择多个文件
		fileObjName:'file',//在后端接受文件的参数名称，如PHP中的$_FILES['file']
		//formData:{'url':url},//发送给服务端的参数，格式：{key1:value1,key2:value2}
		//fileSizeLimit:2048,//允许上传的文件大小，单位KB
		showUploadedPercent:true,//是否实时显示上传的百分比，如20%
		showUploadedSize:true,//是否实时显示已上传的文件大小，如1M/2M
		removeTimeout:9999999,//上传完成后进度条的消失时间，单位毫秒
		itemTemplate: template, //上传队列模板
		uploader:upUrl,//文件提交的地址
		onUploadStart:function(file){
			
		},
		onInit:function(obj){
			//console.log('初始化');
			if(img != null && img != ''){
				var html = '<div id="fileupload_1_1" class="uploadify-queue-item">'
					+'<span class="delbtn">×</span>'
					+'<div class="showthumb"><img src="'+img+'"></div>'
					+'<input type="hidden" class="thumb" name="'+picName+'" value="'+img+'"/>'
					+'</div>';
				$('#'+id).find('.uploadify-queue').html(html);
			}
		},
		onUploadComplete:function(file,data){
			 var data = $.parseJSON(data);
			 if(data.status == 1){
			 		$('#'+id).find('#fileupload_1_'+file.index).prev('.uploadify-queue-item').css('display','none');
			 		$('#'+id).find('#fileupload_1_'+file.index).prev('.uploadify-queue-item').find('.thumb').remove();
			 	 	$('#'+id).find('#fileupload_1_'+file.index).find('.thumb').val(data.url);
					$('#'+id).find('#fileupload_1_'+file.index).find('.showthumb').html('<img src="'+data.url+'">');

			 }else{
			 	alert(data.msg);
			 }
		},
		onCancel:function(file){
			console.log(file);
		},
		onClearQueue:function(queueItemCount){
			//console.log('有'+queueItemCount+'个文件被删除了');
		},
		onDestroy:function(){
			 
		},
		onSelect:function(file){
			//console.log(file.name+'加入上传队列');

		},
		onQueueComplete:function(queueData){
			//console.log('队列中的文件全部上传完成',queueData);
		}
	});
	
	// 图片删除
	$('#'+id).on('click','.delbtn',function(){
		var queue = $(this).closest('.uploadify-queue-item');
		var path = queue.find('.thumb').val();
		var url = delUrl;
		$.post(url ,{'path':path},function(data){console.log(data);
//			var data = $.parseJSON(data);
			if(data.status == 1){
				queue.remove();
			}else{
				alert(data.msg);
				queue.remove();
			}
		});
	});

	
}	
 
//多图片上传
function upimages(id , picName ,upUrl , delUrl , imgs){
	//上传队列模板
	var template = '<div id="\${fileID}" class="uploadify-queue-item">'
				+'<span class="delbtn">×</span>'
				+'<div class="showthumbs"></div>'
				+'<span class="up_filename">\${fileName}</span>'
				+'<input type="hidden" class="thumbs" name="'+picName+'[]" value=""/>'
				+'</div>';
	
	var up = $('#'+id).Huploadify({
		auto:true,//是否开启自动上传
		buttonText:'选择图片',//上传按钮上的文字
		fileTypeExts:'*.jpg;*.png;*.gif;*.jpeg;',//允许上传的文件类型，格式'*.jpg;*.doc'
		multi:true,//是否允许选择多个文件
		fileObjName:'file',//在后端接受文件的参数名称，如PHP中的$_FILES['file']
		//formData:{'url':url},//发送给服务端的参数，格式：{key1:value1,key2:value2}
		//fileSizeLimit:2048,//允许上传的文件大小，单位KB
		showUploadedPercent:true,//是否实时显示上传的百分比，如20%
		showUploadedSize:true,//是否实时显示已上传的文件大小，如1M/2M
		removeTimeout:9999999,//上传完成后进度条的消失时间，单位毫秒
		itemTemplate: template, //上传队列模板
		uploader:upUrl,//文件提交的地址
		onUploadStart:function(file){
			//console.log(file.name+'开始上传');
		},
		onInit:function(obj){
			if(imgs != null && imgs != ''){
				var html = '';
				var image = imgs.split(',');
				 
				$(image).each(function(i){
					if(this != null && this != ''){
					 html += '<div id="fileupload_1_'+(i+1*1)+'" class="uploadify-queue-item">'
						+'<span class="delbtn">×</span>'
						+'<div class="showthumbs"><img src="'+this+'"></div>'
						+'<input type="hidden" class="thumbs" name="'+picName+'[]" value="'+this+'"/>'
						+'</div>';
					}
				});
	 
				$('#'+id).find('.uploadify-queue').html(html);
			}
		},
		onUploadComplete:function(file,data){
//			 var data = $.parseJSON(data);
			 if(data.status == 1){
			 	 	$('#'+id).find('#fileupload_1_'+file.index).find('input.thumbs').val(data.url);
					$('#'+id).find('#fileupload_1_'+file.index).find('.showthumbs').html('<img src="'+data.url+'">');

			 }else{
			 	alert(data.msg);
			 }
		},
		onCancel:function(file){
			console.log(file);
		},
		onClearQueue:function(queueItemCount){
			//console.log('有'+queueItemCount+'个文件被删除了');
		},
		onDestroy:function(){
			 
		},
		onSelect:function(file){
			//console.log(file.name+'加入上传队列');
		},
		onQueueComplete:function(queueData){
			//console.log('队列中的文件全部上传完成',queueData);
		}
	});
	
	// 图片删除
	$('#'+id).on('click','.delbtn',function(){
		var queue = $(this).closest('.uploadify-queue-item');
		var path = queue.find('.thumbs').val();
		var url = delUrl;
		$.post(url ,{'path':path},function(data){
//			var data = $.parseJSON(data);
			if(data.status == 1){
				queue.hide();
				queue.find('.thumbs').remove();
			}else{
				alert(data.msg);
				queue.hide();
				queue.find('.thumbs').remove();
			}
		});
	});

 
}

 
//多视频上传
function upvideos(id , picName ,upUrl , delUrl , imgs){
	//上传队列模板
	var template = '<div id="\${fileID}" class="uploadify-queue-item">'
				+'<span class="delbtn">×</span>'
				+'<div class="showvideo"></div>'
				+'<div class="uploadify-progress">'
				+'<div style="width: 100%;" class="uploadify-progress-bar"></div>'
				+'</div>'
				+'<input type="hidden" class="thumbs" name="'+picName+'[]" value=""/>'
				+'</div>';
	
	var up = $('#'+id).Huploadify({
		auto:true,//是否开启自动上传
		buttonText:'选择视频',//上传按钮上的文字
		fileTypeExts:'*.mp4;*.MOV;*.mov',//允许上传的文件类型，格式'*.jpg;*.doc'
		multi:true,//是否允许选择多个文件
		fileObjName:'file',//在后端接受文件的参数名称，如PHP中的$_FILES['file']
		//formData:{'url':url},//发送给服务端的参数，格式：{key1:value1,key2:value2}
		fileSizeLimit:2048*1024*100,//允许上传的文件大小，单位KB
		showUploadedPercent:true,//是否实时显示上传的百分比，如20%
		showUploadedSize:false,//是否实时显示已上传的文件大小，如1M/2M
		removeTimeout:9999999,//上传完成后进度条的消失时间，单位毫秒
		itemTemplate: template, //上传队列模板
		uploader:upUrl,//文件提交的地址
		onUploadStart:function(file){
			//console.log(file.name+'开始上传');
		},
		onInit:function(obj){
			if(imgs != null && imgs != ''){
				var html = '';
				var image = imgs.split(',');
				$(image).each(function(i){
					var video = this.split('\/');
					//console.log(video);
					 html += '<div id="fileupload_1_'+(i+1*1)+'" class="uploadify-queue-item">'
						+'<span class="delbtn">×</span>'
						+'<div class="showvideo">'+video[3]+'</div>'
						+'<input type="hidden" class="thumbs" name="'+picName+'[]" value="'+this+'"/>'
						+'</div>';
				});
	 
				$('#'+id).find('.uploadify-queue').html(html);
			}
		},
		onUploadComplete:function(file,data){
//			 var data = $.parseJSON(data);
			 if(data.status == 1){
			 	 	$('#'+id).find('#fileupload_1_'+file.index).find('input.thumbs').val(data.url);
					$('#'+id).find('#fileupload_1_'+file.index).find('.showvideo').html(data.name);

			 }else{
			 	alert(data.msg);
			 }
		},
		onCancel:function(file){
			console.log(file);
		},
		onClearQueue:function(queueItemCount){
			//console.log('有'+queueItemCount+'个文件被删除了');
		},
		onDestroy:function(){
			 
		},
		onSelect:function(file){
			//console.log(file.name+'加入上传队列');
		},
		onQueueComplete:function(queueData){
			//console.log('队列中的文件全部上传完成',queueData);
		}
	});
	
	// 删除
	$('#'+id).on('click','.delbtn',function(){
		var queue = $(this).closest('.uploadify-queue-item');
		var path = queue.find('.thumbs').val();
		var url = delUrl;
		$.post(url ,{'path':path},function(data){
//			var data = $.parseJSON(data);
			if(data.status == 1){
				queue.hide();
				queue.find('.thumbs').remove();
			}else{
				alert(data.msg);
				queue.hide();
				queue.find('.thumbs').remove();
			}
		});
	});

 
}

 
 

 
//单视频上传
function upvideo(id , picName ,upUrl , delUrl , imgs){
	//上传队列模板
	var template = '<div id="\${fileID}" class="uploadify-queue-item">'
				+'<span class="delbtn">×</span>'
				+'<div class="showvideo"></div>'
				+'<div class="uploadify-progress">'
				+'<div style="width: 100%;" class="uploadify-progress-bar"></div>'
				+'</div>'
				+'<input type="hidden" class="thumb" name="'+picName+'" value=""/>'
				+'</div>';
	
	var up = $('#'+id).Huploadify({
		auto:true,//是否开启自动上传
		buttonText:'选择文件',//上传按钮上的文字
		fileTypeExts:'*.mp4;*.MOV;*.mov;*.mp3;*.MP3',//允许上传的文件类型，格式'*.jpg;*.doc'
		multi:false,//是否允许选择多个文件
		fileObjName:'file',//在后端接受文件的参数名称，如PHP中的$_FILES['file']
		//formData:{'url':url},//发送给服务端的参数，格式：{key1:value1,key2:value2}
		fileSizeLimit:2048*1024*100,//允许上传的文件大小，单位KB
		showUploadedPercent:true,//是否实时显示上传的百分比，如20%
		showUploadedSize:false,//是否实时显示已上传的文件大小，如1M/2M
		removeTimeout:9999999,//上传完成后进度条的消失时间，单位毫秒
		itemTemplate: template, //上传队列模板
		uploader:upUrl,//文件提交的地址
		onUploadStart:function(file){
			//console.log(file.name+'开始上传');
		},
		onInit:function(obj){
			if(imgs != null && imgs != ''){
				var html = '';
				var video = imgs.split('\/');
				var l = video.length;
				 
				 html += '<div id="fileupload_1_1" class="uploadify-queue-item">'
					+'<span class="delbtn">×</span>'
					+'<div class="showvideo">'+video[l-1]+'</div>'
					+'<input type="hidden" class="thumb" name="'+picName+'" value="'+imgs+'"/>'
					+'</div>';
	 
				$('#'+id).find('.uploadify-queue').html(html);
			}
		},
		onUploadComplete:function(file,data){
//			 var data = $.parseJSON(data);
			 
			 if(data.status == 1){
				 $('#'+id).find('#fileupload_1_'+file.index).prev('.uploadify-queue-item').find('.thumb').remove();
				 	$('#'+id).find('#fileupload_1_'+file.index).prev('.uploadify-queue-item').css('display','none');
			 		
			 	 	$('#'+id).find('#fileupload_1_'+file.index).find('.thumb').val(data.url);
					$('#'+id).find('#fileupload_1_'+file.index).find('.showvideo').html(data.name);
					// $('#'+id).find('#fileupload_1_'+file.index).find('.uploadify-progress div').css('width','100%');
					// $('#'+id).find('#fileupload_1_'+file.index).find('.up_percent').html('100%');	

			 }else{
			 	alert(data.msg);
			 }
		},
		onCancel:function(file){
			console.log(file);
		},
		onClearQueue:function(queueItemCount){
			//console.log('有'+queueItemCount+'个文件被删除了');
		},
		onDestroy:function(){
			 
		},
		onSelect:function(file){
			//console.log(file.name+'加入上传队列');
		},
		onQueueComplete:function(queueData){
			//console.log('队列中的文件全部上传完成',queueData);
		}
	});
	
	// 删除
	$('#'+id).on('click','.delbtn',function(){
		var queue = $(this).closest('.uploadify-queue-item');
		var path = queue.find('.thumb').val();
		var url = delUrl;
		$.post(url ,{'path':path},function(data){
//			var data = $.parseJSON(data);
			if(data.status == 1){
				queue.hide();
				queue.find('.thumb').remove();
			}else{
				alert(data.msg);
				queue.hide();
				queue.find('.thumb').remove();
			}
		});
	});

 
}

 
 
 
 
//单文件上传
function upfile(id , picName ,upUrl , delUrl , imgs){
	//上传队列模板
	var template = '<div id="\${fileID}" class="uploadify-queue-item">'
				+'<span class="delbtn">×</span>'
				+'<div class="showvideo"></div>'
				+'<div class="uploadify-progress">'
				+'<div style="width: 100%;" class="uploadify-progress-bar"></div>'
				+'</div>'
				+'<input type="hidden" class="thumb" name="'+picName+'" value=""/>'
				+'</div>';
	
	var up = $('#'+id).Huploadify({
		auto:true,//是否开启自动上传
		buttonText:'选择文件',//上传按钮上的文字
		fileTypeExts:'*.apk;',//允许上传的文件类型，格式'*.jpg;*.doc'
		multi:false,//是否允许选择多个文件
		fileObjName:'file',//在后端接受文件的参数名称，如PHP中的$_FILES['file']
		//formData:{'url':url},//发送给服务端的参数，格式：{key1:value1,key2:value2}
		fileSizeLimit:1024*1024*100,//允许上传的文件大小，单位KB
		showUploadedPercent:true,//是否实时显示上传的百分比，如20%
		showUploadedSize:false,//是否实时显示已上传的文件大小，如1M/2M
		removeTimeout:9999999,//上传完成后进度条的消失时间，单位毫秒
		itemTemplate: template, //上传队列模板
		uploader:upUrl,//文件提交的地址
		onUploadStart:function(file){
			//console.log(file.name+'开始上传');
		},
		onInit:function(obj){
			if(imgs != null && imgs != ''){
				var html = '';
				var video = imgs.split('\/');
				var l = video.length;
				 
				 html += '<div id="fileupload_1_1" class="uploadify-queue-item">'
					+'<span class="delbtn">×</span>'
					+'<div class="showvideo">'+video[l-1]+'</div>'
					+'<input type="hidden" class="thumb" name="'+picName+'" value="'+imgs+'"/>'
					+'</div>';
	 
				$('#'+id).find('.uploadify-queue').html(html);
			}
		},
		onUploadComplete:function(file,data){
			 var data = $.parseJSON(data);
			 
			 if(data.status == 1){
				 $('#'+id).find('#fileupload_1_'+file.index).prev('.uploadify-queue-item').find('.thumb').remove();
				 	$('#'+id).find('#fileupload_1_'+file.index).prev('.uploadify-queue-item').css('display','none');
			 		
			 	 	$('#'+id).find('#fileupload_1_'+file.index).find('.thumb').val(data.data.url);
					$('#'+id).find('#fileupload_1_'+file.index).find('.showvideo').html(data.data.name);
					// $('#'+id).find('#fileupload_1_'+file.index).find('.uploadify-progress div').css('width','100%');
					// $('#'+id).find('#fileupload_1_'+file.index).find('.up_percent').html('100%');	

			 }else{
			 	alert(data.msg);
			 }
		},
		onCancel:function(file){
			console.log(file);
		},
		onClearQueue:function(queueItemCount){
			//console.log('有'+queueItemCount+'个文件被删除了');
		},
		onDestroy:function(){
			 
		},
		onSelect:function(file){
			//console.log(file.name+'加入上传队列');
		},
		onQueueComplete:function(queueData){
			//console.log('队列中的文件全部上传完成',queueData);
		}
	});
	
	// 删除
	$('#'+id).on('click','.delbtn',function(){
		var queue = $(this).closest('.uploadify-queue-item');
		var path = queue.find('.thumb').val();
		var url = delUrl;
		$.post(url ,{'path':path},function(data){
			//var data = $.parseJSON(data);
			if(data.status == 1){
				queue.hide();
				queue.find('.thumb').remove();
			}else{
				alert(data.msg);
				queue.hide();
				queue.find('.thumb').remove();
			}
		});
	});

} 