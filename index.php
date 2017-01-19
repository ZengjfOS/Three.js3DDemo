<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>
        AplexOS 3D Demo
    </title>
    <style>
        <!-- 设置边距0，为了全屏显示 -->
        body {
            margin: 0;
        }
        
        <!-- 设置canvas大小，填充屏幕 -->
        canvas {
            width: 100%;
            height: 100%;
        }
        
        <!-- 设置对应id的宽高 -->
        #airPassSensor,
        #humiditySensor,
        #noiseSensor {
            width: 200px;
            height: 200px;
            display: inline-block;
            margin: 1em;
        }
    </style>
    <link href="css/styles.css" rel="stylesheet" type="text/css" />
    
    <!--copy this code into your header-->
    <!-- 温度计 -->
    <link href="css/goal-thermometer.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="js/goal-thermometer.js"></script>
    <script type="text/javascript">
        var currentAmount = 26.4;
    </script>
    <script src="http://code.jquery.com/jquery-latest.js"></script>
    <!--end copy-->

    <!-- three.js lib for javascript -->
    <script src="js/libs/tween.min.js"></script>
    <script src="js/renderers/Projector.js"></script>
    <script src="js/renderers/CanvasRenderer.js"></script>
    <script src="js/three.js"></script>
    <script src="js/controls/OrbitControls.js"></script>
    <script src="js/ThreeBSP.js"></script>
    <script type="text/javascript" src="libs/stats.js"></script>
    <script type="text/javascript" src="libs/dat.gui.js"></script>

    <!-- <script type="text/javascript" src="js/jquery.min.js"></script> -->
    <!-- 4个传感器模型，桌椅模型，盆栽模型 -->
    <script type="text/javascript" src="js/aplex.3d.model.js"></script>

    <!-- Canvas仪表盘模型库 -->
    <script src="js/raphael-2.1.4.min.js"></script>
    <script src="js/justgage.js"></script>
</head>
<body>
	<script>  

	  	var temp;                               // 温度
	  	var airpass;                            // 大气压
	  	var humidity;                           // 湿度
	  	var microphone;                         // 噪声
        var scene;                              // three.js场景
        var isDoorClose = true;                 // 关门动画标志

        //初始化场景
        function initScene(){  
            scene = new THREE.Scene();  
        }  

        //更新缩放，旋转的效果
        function updateControls() {  
            controls.update();  
        }  

        //声明摄像机，初始化摄像机
        var camera;  
        function initCamera(){  
            camera = new THREE.PerspectiveCamera( 45, window.innerWidth/window.innerHeight, 0.1, 1000);  
            // 将摄像头放在x:100，y:100，z:0的地方，摄像头聚焦于x:0，y:0，z:0
            camera.position.z = 0;  
            camera.position.y = 100;  
            camera.position.x = 100;  
            camera.lookAt({x:0,y:0,z:0});  

            // 控制摄像头自动更新
            controls = new THREE.OrbitControls( camera );  
            controls.addEventListener( 'change', updateControls );  
        }  

        //声明渲染器，初始化渲染器
        var renderer;  
        function initRender(){  
            renderer = new THREE.WebGLRenderer({antialias: true});  
            renderer.setSize( window.innerWidth, window.innerHeight );  
            document.body.appendChild( renderer.domElement );  
            renderer.setClearColor(0x91BCE5, 1.0);  
        }  

        //声明灯光，初始化灯光
        var light;
        function initLight() {
            // 设置环境光亮度，声明初始化环境光灯，添加入场景
        	var ambiColor = "#ffffff";
        	var ambientLight = new THREE.AmbientLight(ambiColor);
        	scene.add(ambientLight)

            // 创建点光源，第二个参数是光源强度，你可以改变它试一下
            light = new THREE.SpotLight(0xffffff);
            // 位置不同，方向光作用于物体的面也不同，看到的物体各个面的颜色也不一样
            light.position.set(0,1000,0);
            light.intensity = 1;
            scene.add(light);
        }

        //初始化整个空间模型，包括：传感器、地板、墙壁、桌椅、盆栽、透明门。
        function initObject(){  
            // 创建地面，并给出纹理图片  
            var floor  = new THREE.BoxGeometry( 100, 100, 1);  
            var texture = THREE.ImageUtils.loadTexture("images/floor.jpg",null,function(t)  
            {  
            });  
            texture.wrapS = texture.wrapT = THREE.RepeatWrapping;           // 材质平铺
            texture.repeat.set(40, 40);                                     // 横向、纵向平铺次数
            var material = new THREE.MeshBasicMaterial({map:texture});      // 用纹理创建材质
            var floorCube = new THREE.Mesh( floor, material );              // 绑定几何图形和材质，生成模型
            scene.add( floorCube );                                         // 添加入场景
            floorCube.rotation.x += 0.5*Math.PI;                            // 地板旋转角度
  
            /************各个墙面设置相同的纹理*********/  
            // 后面变量都是cube，这个因为不会引用到，所以没事
            
            // 创建墙面材料  
            var texture_wall = THREE.ImageUtils.loadTexture("images/wall.jpg",null,function(t)  
			{  
            }); 
            texture_wall.wrapS = texture_wall.wrapT = THREE.RepeatWrapping;  
            texture_wall.repeat.set(7,1);  
            var material_wall = new THREE.MeshBasicMaterial({map:texture_wall});  

            // 添加1号墙面  
            var cubeGeometry = new THREE.BoxGeometry(1, 16, 80);   
            var cube = new THREE.Mesh( cubeGeometry, material_wall );   
            cube.position.x = -40;  
            cube.position.y = 8;  
            cube.position.z = 0;  
            scene.add(cube);  
  
            // 墙面2  
            var cubeGeometry = new THREE.BoxGeometry(1, 16, 81);  
            var cube = new THREE.Mesh( cubeGeometry, material_wall );  
            cube.position.x = 0;  
            cube.position.y = 8;  
            cube.position.z = 40;  
            cube.rotation.y += 0.5*Math.PI;  
            scene.add(cube);  
  
            // 墙面2  
            var cubeGeometry = new THREE.BoxGeometry(1, 16, 80);  
            var cube = new THREE.Mesh( cubeGeometry, material_wall );  
            cube.position.x = 40;  
            cube.position.y = 8;  
            cube.position.z = 0;  
            scene.add(cube);  
  
            // 墙面3  
            var cubeGeometry = new THREE.BoxGeometry(1, 16, 81);  
            var cube = new THREE.Mesh( cubeGeometry,material_wall );            // 设置墙面位置  
            cube.position.x = 0;  
            cube.position.y = 8;  
            cube.position.z = -40;  
            cube.rotation.y += 0.5*Math.PI;  

            // 透明门  
            var door = new THREE.BoxGeometry(1, 14, 10);  
            var door_cube = new THREE.Mesh( door);  
            door_cube.position.x = 0;  
            door_cube.position.y = 7;  
            door_cube.position.z = -40;  
            door_cube.rotation.y += 0.5*Math.PI;  
  
            // 从墙里把门建立出来
            var sphere1BSP = new ThreeBSP(cube);            // 从3号墙里扣除一个门来。
            var cube2BSP = new ThreeBSP(door_cube);         
            resultBSP = sphere1BSP.subtract(cube2BSP);  
            result = resultBSP.toMesh(material_wall);  
            result.material.shading = THREE.FlatShading;  
            result.geometry.computeFaceNormals();  
            result.geometry.computeVertexNormals();  
            result.material.needsUpdate = true;  
            result.geometry.buffersNeedUpdate = true;  
            result.geometry.uvsNeedUpdate = true;  
            scene.add(result);  

            // 设置透明门位置  
            var door = new THREE.BoxGeometry(0.1, 14, 10);  
            var door_cube1 = new THREE.Mesh( door,new THREE.MeshBasicMaterial( { color:0x555555, opacity: 0.5,transparent: true } ));  
            door_cube1.position.x = 0;  
            door_cube1.position.y = 7;  
            door_cube1.position.z = -40.5;  
            door_cube1.rotation.y += 0.5*Math.PI;
            scene.add(door_cube1);  
            

            // 调用aplex.3d.model.js封装库，这个类主要是用于简化网络访问，以及场景渲染。
            var modelfactory = new Aplex3DModel();
            // 桌椅  
            modelfactory.createModelwithJsonFile(scene,"json/desk_chair.json","assets/models/aplex_desk.js");
            // 工具桌
            modelfactory.createModelwithJsonFile(scene,"json/desk_withoutchair.json","assets/models/aplex_desk_withoutchair.js");
        	// 空调
            modelfactory.createModelwithAxis(scene,"assets/models/air-conditioning-in.js" ,-39 ,13 ,-30 ,0.5);
            modelfactory.createModelwithAxis(scene,"assets/models/air-conditioning-out.js" ,-30 ,11 ,-42 ,1);
            // 麦克风
            modelfactory.createModelwithAxis(scene,"assets/models/microphone.js" ,0,8.7,0 ,1,function(model){
            		microphone = model;
           		}
           	);
            modelfactory.createModelwithAxis(scene,"assets/models/meter.js" ,-39 ,10 ,-20 ,0.5);
            // 大气压传感器
            modelfactory.createModelwithAxis(scene,"assets/models/yibiao.js" ,-39 ,9 ,-3 ,1,function(model){
            		airpass = model;
               	}
           	);
           	// 湿度传感器
            modelfactory.createModelwithAxis(scene,"assets/models/humidity.js" ,-39 ,9 ,-9 ,0,function(model){
        			humidity = model;
           		}
       		);
//         	// 盆摘
            modelfactory.createFlowerModeWithAxis(scene,-7,1,-35);
            modelfactory.createFlowerModeWithAxis(scene,7,1,-35);
        	// 温度计
            modelfactory.createModelwithAxis(scene,"assets/models/temp.js" ,-39 ,8 ,-15 ,0,function(model){
            		temp = model;
                }
            );
            
            // 鼠标事件监测，当鼠标滑过3D模型时，触发对应的事件
            var raycaster = new THREE.Raycaster();
			var mouse = new THREE.Vector2();
            // 添加对应事件的处理函数
            document.addEventListener('mousemove', onMouseOver, false );
            document.addEventListener('dblclick', onDblClick, false );
            window.addEventListener( 'resize', onWindowResize, false );

            // 给门添加双击打开，双击关闭事件
            function onDblClick(event) {
            	event.preventDefault();
    			mouse.x = ( event.clientX / renderer.domElement.clientWidth ) * 2 - 1;
    			mouse.y = - ( event.clientY / renderer.domElement.clientHeight ) * 2 + 1;
    			raycaster.setFromCamera( mouse, camera );
    			var intersects = raycaster.intersectObjects(scene.children);
    			if ( intersects.length > 0 ) {
    				if(intersects[0].object === door_cube1){
        				if(isDoorClose){
        					door_cube1.rotation.y += 0.75*Math.PI;  
        					door_cube1.position.x -= (Math.sqrt(12.5) + 5); // door_cube1.position.z -= Math.sqrt(5);
        					door_cube1.position.z -= Math.sqrt(12.5);
        				}else{
        					door_cube1.position.z += Math.sqrt(12.5);
        					door_cube1.position.x += (5 + Math.sqrt(12.5));
        					door_cube1.rotation.y -= 0.75*Math.PI;  
            			}
            			isDoorClose = !isDoorClose;
    				}
    			}
            }

            //给温度计添加mouseover事件
            function onMouseOver(event) {
    			event.preventDefault();
    			mouse.x = ( event.clientX / renderer.domElement.clientWidth ) * 2 - 1;
    			mouse.y = - ( event.clientY / renderer.domElement.clientHeight ) * 2 + 1;
    			raycaster.setFromCamera( mouse, camera );
    			var intersects = raycaster.intersectObjects(scene.children);
    			if ( intersects.length > 0 ) {
    				if(intersects[0].object === temp||intersects[0].object === airpass||
    	    				intersects[0].object === humidity||intersects[0].object === microphone){
   					 	document.getElementsByTagName("body").item(0).style.cursor="pointer";
//     					$("#code").center();
    					$("#code").children().hide();
    					if(intersects[0].object === temp){
							$('#goal-thermometer').show();
    					}else if(intersects[0].object === airpass){
    						$('#airPassSensor').show();
        				}else if(intersects[0].object === humidity){
    						$('#humiditySensor').show();
        				}else{
    						$('#noiseSensor').show();
        				}
						$('#code').css({'left':event.clientX+10+'px','top':event.clientY-100+'px'});
     			    	$('#code').fadeIn();
    				}else{
    					document.getElementsByTagName("body").item(0).style.cursor="default";
     					$('#code').hide();
    				}
    			}
    		}

            // 窗体重绘
            function onWindowResize() {
				camera.aspect = window.innerWidth / window.innerHeight;
				camera.updateProjectionMatrix();
				renderer.setSize( window.innerWidth, window.innerHeight );
			}
        }  

        function drawShape(){  
            var shape = new THREE.Shape();  
            shape.moveTo(10, 10);  
            shape.lineTo(10, 40);  
            shape.lineTo(40, 40);  
            shape.lineTo(10, 10);  
            return shape;  
        }  

        function render() {  
            // 设置渲染对象
            requestAnimationFrame(render);  
            // 自动渲染
            renderer.render( scene, camera );  
        }  

        function init(){  
            initRender();  
            initScene();  
            initCamera();  
            initLight();  
            initObject();  
        }  
        
        // 初始化系统
        init();  
        // 渲染系统
        render();  
  
        var airPassSensor;                  // 空气压力传感器
        var humiditySensor;                 // 湿度传感器
        var noiseSensor;                    // 噪声传感器

        //弹出框框架
        $(document).ready(function(){ 
            $('#code').hide();
            airPassSensor = new JustGage({
    	    	id: "airPassSensor", 
    	    	value: 50, 
    	        min: 30,
    	        max: 110,
    	        titleFontColor:"#ffffff",
    	        labelFontColor:"#ffffff",
    	        title: "ATM",
    	        label: "kpa",
    	  		levelColors: [
    	  			"#222222",
    	  			"#555555",
    	  			"#CCCCCC"
    	  		]   
    	    });
            humiditySensor = new JustGage({
    	    	id: "humiditySensor", 
    	        titleFontColor:"#ffffff",
    	        labelFontColor:"#ffffff",
    	        value: 60, 
    	        min: 0,
    	        max: 100,
    	        title: "humidity",
    	        label: "%",
    	    });
            noiseSensor = new JustGage({
    	    	id: "noiseSensor", 
    	        titleFontColor:"#ffffff",
    	        labelFontColor:"#ffffff",
    	        value: 40, 
    	        min: 30,
    	        max: 120,
    	        title: "noise",
    	        label: "dB",
    	  		levelColors: [
    	  			"#222222",
    	  			"#555555",
    	  			"#CCCCCC"
    	  		]
    	    });

            // 刷新一次传感器数据
            getSensorInfo();

            // 每3秒刷新一次界面，数据是随机数据
    	 	window.setInterval("getSensorInfo()",3000);
        }); 

        // 刷新传感器
        function getSensorInfo(){
        	airPassSensor.refresh(100*Math.random());
        	humiditySensor.refresh(100*Math.random());
        	noiseSensor.refresh(100*Math.random());
        	_startAnim();
        }
        //模拟温度变化
        function _startAnim(){
        	currentAmount = parseInt(40*Math.random());
        	startAnim();
        }

        $(function() {
            // 鼠标悬浮弹窗。
            jQuery.fn.center = function(loaded) {
                var obj = this;
                body_width = parseInt($(window).width());
                body_height = parseInt($(window).height());
                block_width = parseInt(obj.width());
                block_height = parseInt(obj.height());

                left_position = parseInt((body_width / 2) - (block_width / 2) + $(window).scrollLeft());
                if (body_width < block_width) {
                    left_position = 0 + $(window).scrollLeft();
                };

                top_position = parseInt((body_height / 2) - (block_height / 2) + $(window).scrollTop());
                if (body_height < block_height) {
                    top_position = 0 + $(window).scrollTop();
                };

                if (!loaded) {
                    obj.css({
                        'position': 'absolute'
                    });
                    obj.css({
                        'top': ($(window).height() - $('#code').height()) * 0.5,
                        'left': left_position
                    });
                    $(window).bind('resize', function() {
                        obj.center(!loaded);
                    });
                    $(window).bind('scroll', function() {
                        obj.center(!loaded);
                    });
                } else {
                    obj.stop();
                    obj.css({
                        'position': 'absolute'
                    });
                    obj.animate({
                        'top': top_position
                    }, 200, 'linear');
                }
            }

        })
    </script>
	<div id="code"
		style="position: absolute; top: 120.5px; left: 569px; display: block;">
		<div id="humiditySensor"></div>
		<div id="goal-thermometer"></div>
		<div id="airPassSensor"></div>
		<div id="noiseSensor"></div>
	</div>
</body>
</html>
