var OSMEX = OSMEX || { REVISION: '1' };

OSMEX.Cube = function ( ) {
 
    THREE.Object3D.call( this );
    this.name = "Cube";
    
    this.pickable = false;

    var meshMaterial = new THREE.MeshPhongMaterial( {
        color: 0x000000, 
        shading: THREE.SmoothShading, 
        ambient: 0xffffff
    } );
       
    var cubeGeometry = new THREE.CubeGeometry( 4, 4, 4 );
    this.cube = new THREE.Mesh( cubeGeometry, meshMaterial );
    this.cube.position.set( 0, 0, 0 );
    this.add( this.cube );
    
    this.position = new THREE.Vector3( 0, 0, 0 );
 
};

OSMEX.Cube.prototype = Object.create( THREE.Object3D.prototype );
