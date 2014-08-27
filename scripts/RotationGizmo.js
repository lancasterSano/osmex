var OSMEX = OSMEX || { REVISION: '1' };

OSMEX.RotationGizmo = function (  ) {
    
    THREE.Object3D.call( this );
    this.name = "RotationGizmo";
    
    this.target = null;
    
    this.overlay = new OSMEX.RotationGizmoOverlay(new THREE.Vector3( 1, 0, 0 ));
    this.add(this.overlay);
    
    this.AxisX     = new OSMEX.RotationTorus( 15, 0.5, new THREE.Vector3( 1, 0, 0 ), 0xff0000, true );  
    this.AxisY     = new OSMEX.RotationTorus( 15, 0.5, new THREE.Vector3( 0, 1, 0 ), 0x00ff00, true );
    this.AxisZ     = new OSMEX.RotationTorus( 15, 0.5, new THREE.Vector3( 0, 0, 1 ), 0x0000ff, true );
    this.AxisFront = new OSMEX.RotationTorus( 15, 0.7, new THREE.Vector3( 1, 0, 0 ), 0xffffff, false );
    
    this.globeContainer = new THREE.Object3D();
    this.globeContainer.add(this.AxisX);	
    this.globeContainer.add(this.AxisY);	
    this.globeContainer.add(this.AxisZ);
    this.add(this.globeContainer);
    
    this.add(this.AxisFront);
    
    this.setTarget(null);
};

OSMEX.RotationGizmo.prototype = Object.create( THREE.Object3D.prototype );

OSMEX.RotationGizmo.prototype.setTarget = function ( target ) {
    
    this.target = target;
    
    var visibility = false;
    var rotationFunc = null;
    
    if ( target ) {
        
        visibility = true;
 
        rotationFunc = function(target) { return function(radians) {
           
           var deltaQuat = new THREE.Quaternion().setFromAxisAngle( this.dir, radians );
           var newQuat = new THREE.Quaternion().setFromEuler( target.rotation, target.eulerOrder ).multiply(deltaQuat);
           
           target.rotation.setEulerFromQuaternion( newQuat, target.eulerOrder );
 
        } }(this.target);
        
    }
    
    this.traverse( function( object ) { object.visible = visibility } );

    this.AxisX.rotationFunc = rotationFunc;
    this.AxisY.rotationFunc = rotationFunc;
    this.AxisZ.rotationFunc = rotationFunc;
    this.AxisFront.rotationFunc = rotationFunc;
}

OSMEX.RotationGizmo.prototype.update = function ( camera ) {
    
    if(this.target){  
        
        var vector = camera.position.clone().sub(this.position);
        
        //this.overlay.setDirection(vector);
        this.overlay.lookAt(camera.position);
    
        this.AxisFront.setDirection(vector);
        
        this.AxisFront.position = vector.clone().normalize().multiplyScalar(2.0);
        
        this.position.copy(this.target.position);
        this.globeContainer.rotation.copy(this.target.rotation);
    }
}
