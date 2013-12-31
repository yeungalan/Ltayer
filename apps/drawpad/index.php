<?php require_once('../../core/require.php'); ?>
<!DOCTYPE html>

<html>
	<head>
		<title>小畫家</title>
		<link rel="stylesheet" href="../../css/jquery-ui-1.10.3.custom.min.css" />
		<style>
			body, input {
				font-size: 9pt;
				margin: 0;
				padding: 0;
			}
			#dCanvas, #dLine {
				clear: both;
			}
			#dCanvas {
				position: fixed;
				top: 0;
				left: 0;
				z-index: -99;
			}
			#controls {
				position: fixed;
				width: auto;
				display: inline-block;
				padding: 1em;
				top: 1em;
				left: 1em;
			}
			.option {
				float: left; width: 20px; height: 20px; border: 2px solid #cccccc;
				margin-right: 4px; margin-bottom: 4px;
			}
			.active {
				border: 2px solid black;
			}
			.lw {
				text-align: center;
				vertical-align: middle;
			}
			img {
				box-shadow: 0 0 10px #ccc;
				display: block;
				margin: 1em auto;
				width: 80%;
			}
			#cSketchPad {
				cursor: arrow;
			}
		</style>
		<script src="../../js/jquery-1.9.1.js" type="text/javascript"></script>
		<script src="../../js/jquery-ui-1.10.3.custom.min.js"></script>
		<script src="../../js/jquery.ui.touch.min.js"></script>
		<script>
			$(function () {
                var p_width, p_color;
				//產生不同顏色的div方格當作調色盤選項
				var colors = "red;orange;yellow;green;blue;indigo;purple;black;white".split(';');
				var sb = [];
				$.each(colors, function (i, v) {
					sb.push("<div class='option' style='background-color:" + v + "'></div>");
				});
				$("#dPallete").html(sb.join("\n"));
				
				//產生不同尺寸的方格當作線條粗細選項
				sb = [];
				for (var i = 1; i <= 9; i++){
                    sb.push(
                        "<div class='option lw'>" +
                        "<div style='margin-top:#px;margin-left:#px;width:%px;height:%px'></div></div>"
                        .replace(/%/g, i)
                        .replace(/#/g, 10 - i / 2));
				}
				$("#dLine").html(sb.join('\n'));
				var $clrs = $("#dPallete .option");
				var $lws = $("#dLine .option");
				
				//點選調色盤時切換焦點並取得顏色存入p_color，
				//同時變更線條粗細選項的方格的顏色
				$clrs.click(function () {
					$clrs.removeClass("active");
					$(this).addClass("active");
					p_color = this.style.backgroundColor;
					$lws.children("div").css("background-color", p_color);
				}).first().click();
				
				//點選線條粗細選項時切換焦點並取得寬度存入p_width
				$lws.click(function () {
					$lws.removeClass("active");
					$(this).addClass("active");
					p_width = $(this).children("div").css("width").replace("px", "");
	
				}).eq(3).click();
				
				//取得canvas context
				var $canvas = $("#cSketchPad");
				var ctx = $canvas[0].getContext("2d");
				
				$canvas[0].width = window.innerWidth;
				$canvas[0].height = window.innerHeight;
				
				$(window).on('resize',function(){
                    var state = ctx.getImageData(0, 0, $canvas[0].width, $canvas[0].height);
					$canvas[0].width = window.innerWidth;
					$canvas[0].height = window.innerHeight;
					ctx.putImageData(state, 0, 0);
				});
				
				ctx.lineCap = "round";
				ctx.fillStyle = "white"; //整個canvas塗上白色背景避免PNG的透明底色效果
				ctx.fillRect(0, 0, $canvas.width(), $canvas.height());
				var drawMode = false;
				
				//canvas點選、移動、放開按鍵事件時進行繪圖動作
				$canvas.on('mousedown touchstart', function(e){
					ctx.beginPath();
					ctx.strokeStyle = p_color;
					ctx.lineWidth = p_width;
					ctx.moveTo((e.pageX || e.originalEvent.targetTouches[0].pageX) - $canvas.position().left, (e.pageY || e.originalEvent.targetTouches[0].pageY) - $canvas.position().top);
					drawMode = true;
				})
				.on('mousemove touchmove', function(e){
					if (drawMode) {
						ctx.lineTo((e.pageX || e.originalEvent.targetTouches[0].pageX) - $canvas.position().left, (e.pageY || e.originalEvent.targetTouches[0].pageY) - $canvas.position().top);
						ctx.stroke();
					}
				})
				.on('mouseup touchend', function(e){
					drawMode = false;
				});
				
				$("#bGenImage").button().click(function (){
                    var data = escape($canvas[0].toDataURL('image/png').split(',')[1]);
                    var xhr = new XMLHttpRequest();
                    
                    xhr.onreadystatechange = function(){
                        if(xhr.status === 200 && xhr.readyState == 4){
                            $("#dOutput").dialog("open");
                        }
                    };
                    
                    xhr.open("POST", "./save.php", true);
                    xhr.send(data);
				});
				
				$("#dOutput").dialog({
					autoOpen: false,
					modal: true
				});
				
				$("#controls").draggable({
					containment: "parent",
					scroll: false
				});
			});
		</script>
	</head>
	<body>
		<div class="ui-state-default ui-corner-all ui-helper-clearfix" id="controls">
			<div id="dPallete"></div>
			<div id="dLine"></div>
			<input type="button" id="bGenImage" value="儲存圖片" />
		</div>
		<div id="dCanvas">
			<canvas id="cSketchPad" width="100%" height="600" />
		</div>
		<div id="dOutput" title="完成">儲存完成!</div>
	</body>
</html>