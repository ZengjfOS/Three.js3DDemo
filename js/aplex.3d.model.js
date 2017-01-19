// 此文件和three.js，jquery.min.js一起放在/js下才能使用,
// 而且引用此文件必须同时引用 three.js，jquery.min.js

// 创建Aplex3dmodel类
function Aplex3DModel() { 
    // 声明加载器
	var loader = new THREE.JSONLoader();
    // 由于Blander本身导出3D模型是JSON文件，所以浏览器要渲染出3D模型，
    // 需要先去Server上那3D模型的JSON文件下来，然后进行渲染。
    // scene：模型放在的场景；
    // JsonUrl：3D模型在浏览器的3D位置信息JSON数据；
    // modelUrl：3D模型的JSON数据；
	this.createModelwithJsonFile = function(scene, JsonUrl, modelUrl){
		$.getJSON(JsonUrl, function(data){          // 先拿位置信息JSON数据
    		$.each(data, function(i, item){         // 可能要放多个模型，迭代
    			var desk1;
                loader.load(modelUrl, function (geometry, mat) {    // 加载模型，也是网络加载
                	desk1 = new THREE.Mesh(geometry, mat[0]);
                	desk1.position.y = item['position_y'];  
                	desk1.scale.set(2,2,2);
                	desk1.rotation.y = desk1.rotation.y + item['rotation_y']*Math.PI;
                	desk1.position.x = item['position_x'];
                	desk1.position.z = item['position_z'];  
                    scene.add(desk1);
                }); 
    		})
        });		
	}

	/**
	 * param scene,modelUrl,x,y,z,rotate
	 * return model
     *
     * 和createModelwithJsonFile函数一样，只是位置信息数据不是放在JSON文件里。
	 */
	this.createModelwithAxis = function(scene,modelUrl,x,y,z,rotate){
		var model;
	    loader.load(modelUrl, function (geometry, mat) {
	    	model = new THREE.Mesh(geometry, mat[0]);
	    	model.position.x = x;  
	    	model.position.z = z;
	    	model.position.y = y;  
	    	model.rotation.y += rotate*Math.PI;
	        scene.add(model);
	    }); 	
	}
	/**
	 * param scene,modelUrl,x,y,z,rotate,callback
	 * return model
     *
     * 重载，加入函数回调，主要是因为有些模型需要被引用，如添加点击事件，改变颜色等等。
	 */
	this.createModelwithAxis = function(scene,modelUrl,x,y,z,rotate,callback){
		var model;
	    loader.load(modelUrl, function (geometry, mat) {
	    	model = new THREE.Mesh(geometry, mat[0]);
	    	model.position.x = x;  
	    	model.position.z = z;
	    	model.position.y = y;  
	    	model.rotation.y += rotate*Math.PI;
	        scene.add(model);
	        if(typeof callback === "function"){
	        	callback(model);
	        }
	    }); 	
	}

	this.createGroupModeWithAxis = function(){

	}

	/**
	 * param scene,x,y,z
	 * return model
     *
     * 使用x、y、z创建盆栽
	 */
	this.createFlowerModeWithAxis = function(scene,x,y,z){
		var pot;
        loader.load('assets/models/pot.js', function (geometry, mat) {
        	pot = new THREE.Mesh(geometry, mat[0]);
        	pot.position.x = x;  
        	pot.position.z = z;
        	pot.position.y = y;  
            scene.add(pot);
        }); 
        var soil;
        loader.load('assets/models/soil.js', function (geometry, mat) {
        	soil = new THREE.Mesh(geometry, mat[0]);
        	soil.position.x = x;  
        	soil.position.z = z;
        	soil.position.y = y;  
            scene.add(soil);
        }); 
        var brench ;
        loader.load('assets/models/brench.js', function (geometry, mat) {
        	brench = new THREE.Mesh(geometry, mat[0]);
        	brench.position.x = x;  
        	brench.position.z = z;
        	brench.position.y = y;  
            scene.add(brench);
        });  
        var leaves;
        loader.load('assets/models/leaves.js', function (geometry, mat) {
        	leaves = new THREE.Mesh(geometry, mat[0]);
        	leaves.position.x = x;  
        	leaves.position.z = z;
        	leaves.position.y = y;  
            scene.add(leaves);
        });
	}
}
