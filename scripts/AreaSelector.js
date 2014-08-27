var OSMEX = OSMEX || { REVISION: '1' };

OSMEX.AreaSelector = function ( onFinishSelecting ) { 
    
    THREE.Object3D.call( this );
    
    this.MIN_LENGTH = 50.0;
    this.MIN_WIDTH = 50.0;
    
    this.MAX_LENGTH = 500.0;
    this.MAX_WIDTH = 500.0;
    
    this.startPos = null;
    this.endPos = null;
    this.areaAllowed = false;
        
    var material = new THREE.MeshBasicMaterial( { color: 0xffff00, transparent: true, opacity: 0.5 } );
    var geometry = new THREE.CubeGeometry( 1, 1, 1 );
     
    this.box = new THREE.Mesh( geometry, material );
    this.box.scale = new THREE.Vector3(1.0, 1.0, 1.0);
    this.box.visible = false;
    this.add(this.box);
    
    this.enabled = false;
    
    this.groundPlane = new THREE.Plane(new THREE.Vector3(0, 1, 0));
    
    this.onFinishSelecting = onFinishSelecting;
};

OSMEX.AreaSelector.prototype = Object.create( THREE.Object3D.prototype );

OSMEX.AreaSelector.prototype.getObjectInfoOverMouse = function ( mouse ) {
    
    var vector = new THREE.Vector3(mouse.x, mouse.y, 0.5);
    
    camera.projectionMatrixInverse.getInverse( camera.projectionMatrix );

    var cameraMatrixWorld = camera.matrixWorld.clone();
    cameraMatrixWorld.elements[12] = 0;
    //cameraMatrixWorld.elements[13] = position.y;
    cameraMatrixWorld.elements[14] = 0;
    var _viewProjectionMatrix = new THREE.Matrix4().multiplyMatrices( cameraMatrixWorld, camera.projectionMatrixInverse );

    vector.applyProjection( _viewProjectionMatrix );
  
    var direction = vector.clone().sub(new THREE.Vector3(0, camera.position.y, 0)).normalize();
    var ray = new THREE.Ray(camera.position, direction);
    var intersectPoint = ray.intersectPlane(this.groundPlane);
    
    return intersectPoint;
};

OSMEX.AreaSelector.prototype.onLeftMouseButtonDown = function ( mouse ) {
    
    if (this.enabled === false)
        return;

    var intersectPoint = this.getObjectInfoOverMouse(mouse);
    
    if (intersectPoint !== undefined) {
    
        if (this.startPos === null) {
            
            this.startPos = intersectPoint.clone();
        }
    }
};

OSMEX.AreaSelector.prototype.onLeftMouseButtonUp = function ( mouse ) {
    
    if (this.enabled === false)
        return;

    var intersectPoint = this.getObjectInfoOverMouse(mouse);
    
    if (intersectPoint !== undefined) {
    
        if (this.endPos) {
            
            this.finishSelecting();
        }
    }
}

OSMEX.AreaSelector.prototype.onMouseMove = function ( mouse ) {

    if (this.enabled === false || this.startPos === null)
        return;
    
    this.box.visible = true;
    
    var intersectPoint = this.getObjectInfoOverMouse(mouse);
    
    if (intersectPoint !== undefined) { 

        this.endPos = intersectPoint.clone();

        var diag = this.endPos.clone().sub(this.startPos);

        //this.box.position = this.startPos.clone().add(diag.clone().divideScalar(2.0));

        var newLen = Math.abs(diag.x);
        var newWidth = Math.abs(diag.z);
        
        newLen = Math.min(newLen, this.MAX_LENGTH);
        newWidth = Math.min(newWidth, this.MAX_WIDTH);

        if (newLen < this.MIN_LENGTH || newWidth < this.MIN_WIDTH) {

            this.areaAllowed = false;
            this.box.material.color = new THREE.Color( 0xff0000 );
        }
        else {
            
            this.areaAllowed = true;
            this.box.material.color = new THREE.Color( 0xffff00 );
        }

        var halfLen = newLen / 2.0;
        this.box.position.x = this.startPos.x + (diag.x < 0 ? -halfLen : halfLen);

        var halfWidth = newWidth / 2.0;
        this.box.position.z = this.startPos.z + (diag.z < 0 ? -halfWidth : halfWidth);

        this.box.scale.x = newLen;
        this.box.scale.z = newWidth;
    }
};

OSMEX.AreaSelector.prototype.startSelecting = function () {
    
    this.box.position = new THREE.Vector3(0, 0, 0);
    this.box.scale = new THREE.Vector3(4.0, 4.0, 4.0);
    this.box.visible = false;
    this.box.material.color = 0xff0000;
    this.areaAllowed = false;
    
    this.enabled = true;
    cameraController.noPan = true;
    
    $("#edit_button").addClass("selected");
};

OSMEX.AreaSelector.prototype.stopSelecting = function () {
    
    this.startPos = null;
    this.endPos = null;
    this.areaAllowed = false;
    
    this.enabled = false;
    this.box.visible = false;
    
    cameraController.noPan = false;
    
    $("#edit_button").removeClass("selected");
};

OSMEX.AreaSelector.prototype.finishSelecting = function () {
    
    if (this.areaAllowed === false)
        return;
    
    var startPoint = this.startPos.clone();
    var endPoint =this.endPos.clone();
    
    this.stopSelecting();
    
    this.onFinishSelecting(startPoint, endPoint);
};
