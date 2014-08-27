var OSMEX = OSMEX || { REVISION: '1' };

OSMEX.Arrow = function ( dir, origin, length, hex, type ) {
    
    THREE.Object3D.call( this );
    this.name = "Arrow";
    
    this.arrowContainer = new THREE.Object3D();
    this.add(this.arrowContainer);
    
    this.pickable = false;
    
    this.dir = null;
    this.setDirection( dir );

    if ( hex === undefined ) hex = 0xffff00;
    if ( length === undefined ) length = 20;
    
    var lineGeometry = new THREE.Geometry();
    lineGeometry.vertices.push( new THREE.Vector3( 0, 0, 0 ) );
    lineGeometry.vertices.push( new THREE.Vector3( 0, 1, 0 ) );

    this.line = new THREE.Line( lineGeometry, new THREE.LineBasicMaterial( {color: hex} ) );
    this.arrowContainer.add( this.line );

    var meshMaterial = new THREE.MeshPhongMaterial( {
        color: hex, 
        shading: THREE.SmoothShading, 
        ambient: 0xffffff
    } );
    this.type = type;
    
    if (this.type == "moving"){
        var coneGeometry = new THREE.CylinderGeometry( 0, 1.5, 7.5, 5, 1 );
        this.cone = new THREE.Mesh( coneGeometry, meshMaterial );
        this.cone.position.set( 0, 30, 0 );
        this.arrowContainer.add( this.cone );
      /* var planeGeometry = new THREE.PlaneGeometry(8,8,8,8);
        this.plane = new THREE.Mesh( planeGeometry, meshMaterial );
        this.plane.position.set( 0, 4, 0 );
        this.plane.rotation.set( 1.5, 1.5, 0);
        this.add( this.plane );*/
        
    }else if (this.type == "sizing"){
        var coneGeometry = new THREE.CylinderGeometry( 0, 1.5, 7.5, 5, 1 );
        this.cone = new THREE.Mesh( coneGeometry, meshMaterial );
        this.cone.position.set( 0, 30, 0 );
        this.arrowContainer.add( this.cone );
     }

    
    if ( origin instanceof THREE.Vector3 ) this.position = origin;
	
    this.len = 0;
    this.defaultLength = length;
    this.setLength( length );
 
};

OSMEX.Arrow.prototype = Object.create( THREE.Object3D.prototype );

OSMEX.Arrow.prototype.setDirection = function ( dir ) {
    
    this.dir = dir.clone().normalize();
    
    var upVector = new THREE.Vector3( 0, 1, 0 );
	
    var cosa = upVector.dot( this.dir );
	
    var axis;
	
    if ( ( cosa < -0.99 ) || ( cosa > 0.99 ) )
    {
        axis = new THREE.Vector3( 1, 0, 0 );
    }
    else
    {
        axis = new THREE.Vector3().crossVectors( upVector, this.dir );
    }
	
    var radians = Math.acos( cosa );
	
    this.arrowContainer.matrix = new THREE.Matrix4().makeRotationAxis( axis, radians );
    this.arrowContainer.rotation.setEulerFromRotationMatrix( this.arrowContainer.matrix, this.arrowContainer.eulerOrder );
};

OSMEX.Arrow.prototype.setLength = function ( length ) {
    
    this.len = length;
    this.line.scale.y = length;
    if (this.type == "sizing")this.cone.position.y = length; 
    
};

OSMEX.Arrow.prototype.restoreDefaultLength = function ( ) {
    
    this.setLength(this.defaultLength);
};

OSMEX.Arrow.prototype.setColor = function ( hex ) {
    
    this.line.material.color.setHex( hex );
    this.cone.material.color.setHex( hex );
   // if (this.type == "moving") this.plane.material.color.setHex( hex );
}; 
