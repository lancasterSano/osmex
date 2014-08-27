var OSMEX = OSMEX || { REVISION: '1' };

OSMEX.Torus = function ( radius, width, dir, hex, useDepth ) {
    
    THREE.Object3D.call( this );
    this.name = "Torus";
	
    this.pickable = false;
    
    this.dir = null;

    if ( hex === undefined ) hex = 0xffff00;

    var meshMaterial = new THREE.MeshPhongMaterial( {
        transparent: true,
        color: hex, 
        shading: THREE.SmoothShading, 
        ambient: 0xffffff,
        opacity: 1.0,
        depthTest: useDepth
    } );
    
    var r = radius || 15;
    var w = width || 0.5;
   
    var torusGeometry = new THREE.TorusGeometry( r, w, 30, 30);
    this.torus = new THREE.Mesh ( torusGeometry, meshMaterial );
    this.add( this.torus );
    
    this.setDirection( dir );
};

OSMEX.Torus.prototype = Object.create( THREE.Object3D.prototype );

OSMEX.Torus.prototype.setDirection = function ( dir ) {
    
    this.dir = dir.clone().normalize();
    
    var upVector = new THREE.Vector3( 0, 0, -1 );
	
    var cosa = upVector.dot( this.dir );
	
    var axis;
	
    if ( ( cosa < -0.99 ) || ( cosa > 0.99 ) )
    {
        axis = new THREE.Vector3( 1, 0, 0 );
    }
    else
    {
        axis = upVector.cross( this.dir ).normalize();
    }
	
    var radians = Math.acos( cosa );
	
    this.torus.matrix = new THREE.Matrix4().makeRotationAxis( axis, radians );
    this.torus.rotation.setEulerFromRotationMatrix( this.torus.matrix, this.torus.eulerOrder );
};

OSMEX.Torus.prototype.setColor = function ( hex ) {
    
    this.torus.material.color.setHex( hex );
};
