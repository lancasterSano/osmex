var OSMEX = OSMEX || { REVISION: '1' };

OSMEX.RotationGizmoOverlay = function ( dir, origin ) {
    
    THREE.Object3D.call( this );
    this.name = "RotationGizmoOverlay";
	
    this.pickable = false;
    
    this.dir = null;
    this.setDirection( dir );  

    var meshMaterial = new THREE.MeshBasicMaterial({ color: 0xaaaa00, transparent: true, opacity: 0.1 });    
   
    var circleGeometry = new THREE.CircleGeometry( 15, 20 );
    this.circle = new THREE.Mesh ( circleGeometry, meshMaterial );
    this.add( this.circle );

    if ( origin instanceof THREE.Vector3 ) this.position = origin;
};

OSMEX.RotationGizmoOverlay.prototype = Object.create( THREE.Object3D.prototype );

OSMEX.RotationGizmoOverlay.prototype.setDirection = function ( dir ) {
    
    this.dir = dir.clone().normalize();
    
    var upVector = new THREE.Vector3( 0, 0, 1 );
	
    var cosa = upVector.dot( this.dir );
	
    var axis;
	
    if ( ( cosa < -0.99 ) || ( cosa > 0.99 ) )
    {
        axis = new THREE.Vector3( 1, 0, 0 );
    }
    else
    {
        axis = upVector.cross( this.dir );
    }
	
    var radians = Math.acos( cosa );
	
    this.matrix = new THREE.Matrix4().makeRotationAxis( axis, radians );
    this.rotation.setEulerFromRotationMatrix( this.matrix, this.eulerOrder );
};
