var OSMEX = OSMEX || { REVISION: '1' };

var BoxBuilderState = {
    NOT_STARTED:    0,
    PICKING_POINT:  1,
    PICKING_LENGTH: 2,
    PICKING_WIDTH:  3,
    PICKING_HEIGHT: 4
};

OSMEX.BoxBuilder = function ( ) { 
    
    THREE.Object3D.call( this );
    
    this.MIN_LENGTH = 1.0;
    this.MIN_WIDTH = 1.0;
    this.MIN_HEIGHT = 1.0;
    
    this.MAX_LENGTH = 300.0;
    this.MAX_WIDTH = 300.0;
    this.MAX_HEIGHT = 300.0;
    
    this.MAX_DELTA_HEIGHT = (this.MAX_HEIGHT - this.MIN_HEIGHT) / 2;
    
    this.currentState = BoxBuilderState.NOT_STARTED;
    
    this.startPos = null;
    this.endPos = null;
    this.centerPos = null;
    
    var material = new THREE.MeshPhongMaterial( { color: 0xff0000, shading: THREE.SmoothShading } );
    var geometry = new THREE.CubeGeometry( 1, 1, 1 );
     
    this.box = new OSMEX.Block( geometry, material );
    this.box.scale = new THREE.Vector3(this.MIN_WIDTH, this.MIN_HEIGHT, this.MIN_LENGTH);
    this.box.visible = false;
    this.add(this.box);
    
    this.text = document.createElement( 'div' );
    this.text.style.position = 'absolute';
    this.text.style.color = '#FF0000';
    this.text.style.fontSize = '16px';
    this.text.style.fontWeight = 'bold';
    document.body.appendChild(this.text);
    
    this.heightPlane = new THREE.Plane();
    this.heightNormal = null;
};

OSMEX.BoxBuilder.prototype = Object.create( THREE.Object3D.prototype );

OSMEX.BoxBuilder.prototype.onLeftClick = function ( mouse ) {
    
    var vector = new THREE.Vector3(mouse.x, mouse.y, 1);

    var projector = new THREE.Projector();
    projector.unprojectVector(vector, camera);
    var ray = new THREE.Ray(camera.position, vector.sub(camera.position).normalize());
    var intersectPoint = ray.intersectPlane(groundPlane);
    var pos3d = intersectPoint.sub(new THREE.Vector3().getPositionFromMatrix(this.matrixWorld));
    
    if (this.currentState !== BoxBuilderState.NOT_STARTED) {
        
        if (this.currentState === BoxBuilderState.PICKING_POINT) {
            
            this.startPos = pos3d.clone();
            
            this.currentState = BoxBuilderState.PICKING_LENGTH;
        }
        
        else if (this.currentState === BoxBuilderState.PICKING_LENGTH) {
            
            this.endPos = pos3d.clone();
            this.centerPos = this.box.position.clone();

            this.currentState = BoxBuilderState.PICKING_WIDTH;
        }
        
        else if (this.currentState === BoxBuilderState.PICKING_WIDTH) {
            
            this.centerPos = this.box.position.clone();
            this.updateHeightPlane();
            
            this.currentState = BoxBuilderState.PICKING_HEIGHT;
        }
        
        else if (this.currentState === BoxBuilderState.PICKING_HEIGHT) {
            
            this.build();
            
            this.currentState = BoxBuilderState.NOT_STARTED;
            
             document.getElementById("dragging").click();
        }
        
        else {
            
            alert("OSMEX.BoxBuilder.prototype.onLeftClick picking error!");
        }
    }
};

OSMEX.BoxBuilder.prototype.onRightClick = function ( pos3d ) {
    
};

OSMEX.BoxBuilder.prototype.onMouseMove = function ( mouse ) {
    
    if (this.currentState !== BoxBuilderState.NOT_STARTED) {
        
        var cx = (mouse.x + 1) / 2 * window.innerWidth;
        var cy = -(mouse.y - 1) / 2 * window.innerHeight;

        this.text.style.left = cx + 16 + 'px';
        this.text.style.top = cy + 4 + 'px';
        
        this.box.visible = true;
    
        var vector = new THREE.Vector3(mouse.x, mouse.y, 1);
        var projector = new THREE.Projector();
        projector.unprojectVector(vector, camera);
        var ray = new THREE.Ray(camera.position, vector.sub(camera.position).normalize());
        var intersectPoint = ray.intersectPlane(groundPlane);
        var pos3d = intersectPoint.sub(new THREE.Vector3().getPositionFromMatrix(this.matrixWorld));
    
        if (this.currentState === BoxBuilderState.PICKING_POINT) {
        
            this.box.position = pos3d;
        }
    
        else if (this.currentState === BoxBuilderState.PICKING_LENGTH) {
        
            var lengthVec = pos3d.clone().sub(this.startPos);
            var len = lengthVec.length();
            var lengthDir = lengthVec.divideScalar(len);
        
            this.box.position = this.startPos.clone();
            this.box.lookAt(pos3d);
        
            if (len > this.MIN_LENGTH) {
                
                len = Math.min(len, this.MAX_LENGTH);
            
                this.box.position.add(lengthDir.multiplyScalar(len / 2));
            }
            else {

                len = this.MIN_LENGTH; // do not rotate the box if a direction vector is very short      
            }
        
            this.box.scale.z = len;
            this.text.innerHTML = 'length: ' + len.toFixed(1) + 'm';
        }
        
        else if (this.currentState === BoxBuilderState.PICKING_WIDTH) {
            
            var dir = this.endPos.clone().sub(this.startPos).normalize();
            var expandVec = pos3d.clone().sub(this.startPos);
            var dot = dir.dot(expandVec);
            
            var intersect = this.startPos.clone().add(dir.clone().multiplyScalar(dot));
            
            var widthVec = pos3d.clone().sub(intersect);
            var width = widthVec.length();
            var widthDir = widthVec.divideScalar(width);
        
            this.box.position = this.centerPos.clone();
        
            if (width > this.MIN_WIDTH) {  
                
                width = Math.min(width, this.MAX_WIDTH);
            
                this.box.position.add(widthDir.multiplyScalar(width / 2));
            }
            else {

                width = this.MIN_WIDTH; // do not rotate the box if a direction vector is very short      
            }
        
            this.box.scale.x = width;
            this.text.innerHTML = 'width: ' + width.toFixed(1) + 'm';
        }
        
        else if (this.currentState === BoxBuilderState.PICKING_HEIGHT) {
            
            var vector = new THREE.Vector3(mouse.x, mouse.y, 0.5);
            projector.unprojectVector(vector, camera);
            var ray = new THREE.Ray(camera.position, vector.sub(camera.position).normalize());
            var intersectPoint = ray.intersectPlane(this.heightPlane);

            if (intersectPoint !== undefined) {
                
                var heightVec = intersectPoint.clone().sub(this.centerPos);
                var height = this.heightNormal.dot(heightVec);
                
                //if (Math.abs(this.box.scale.y - height) < this.MAX_DELTA_HEIGHT) {

                    this.box.position = this.centerPos.clone();

                    if (height > this.MIN_HEIGHT) {

                        height = Math.min(height, this.MAX_HEIGHT);

                        this.box.position.add(this.heightNormal.clone().multiplyScalar(height / 2));
                    }
                    else {

                        height = this.MIN_HEIGHT; // do not rotate the box if a direction vector is very short      
                    }

                    this.box.scale.y = height;
                    this.text.innerHTML = 'height: ' + height.toFixed(1) + 'm';
                //}
            }  
           
        }
    }
};

OSMEX.BoxBuilder.prototype.startBuild = function () {
    
    this.currentState = BoxBuilderState.PICKING_POINT;
    
    this.box.position = new THREE.Vector3(0, 0, 0);
    this.box.rotation = new THREE.Vector3(0, 0, 0);
    this.box.scale = new THREE.Vector3(this.MIN_WIDTH, this.MIN_HEIGHT, this.MIN_LENGTH);
    this.box.visible = false;
    
    cameraController.noPan = true;
    this.text.style.visibility = 'visible';
    this.text.innerHTML = '';
};

OSMEX.BoxBuilder.prototype.build = function () {
    
    var buildedBox = this.box.clone();
    buildedBox.material = new THREE.MeshPhongMaterial( { color: 0xffffff, shading: THREE.SmoothShading } );
    if ($("#BBox").prop("checked")) buildedBox.bbox.setVisibility(true);
    buildedBox.name = "cube";
    buildedBox.typeID = 1;
    buildedBox.isCreated = true;
    buildedBox.isModified = false;
    buildedBox.isDeleted = false;
    objectScene.add(buildedBox);

    this.finishBuild();
};

OSMEX.BoxBuilder.prototype.finishBuild = function () {
    
    this.currentState = BoxBuilderState.NOT_STARTED;
    this.box.visible = false;
    
    cameraController.noPan = false;
    this.text.style.visibility = 'hidden';
    
    $('#build_box').removeClass('selected');
};

OSMEX.BoxBuilder.prototype.isBuilding = function () {
    
    return this.currentState !== BoxBuilderState.NOT_STARTED;
};

OSMEX.BoxBuilder.prototype.updateHeightPlane = function () {
    
    var boxPlane = new THREE.Triangle(this.startPos, this.endPos, this.centerPos);
    this.heightNormal = boxPlane.normal();
    
    if (this.up.dot(this.heightNormal) < 0) {
        
        this.heightNormal.negate(); // prevent picking height on negative Y
    }

    var cameraDir = camera.position.clone().sub(this.centerPos).normalize();
    var rightDir = cameraDir.clone().cross(this.heightNormal).normalize();
    var forwardDir = this.heightNormal.clone().cross(rightDir).normalize();

    this.heightPlane.setFromNormalAndCoplanarPoint(forwardDir, this.centerPos);
};

OSMEX.BoxBuilder.prototype.update = function () {
    
    if (this.currentState === BoxBuilderState.PICKING_HEIGHT) {
        
        this.updateHeightPlane();
    }
};
