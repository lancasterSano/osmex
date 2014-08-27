var OSMEX = OSMEX || { REVISION: '1' };

OSMEX.MovingGizmoPlane = function ( dir, hex ) {
    
    THREE.Object3D.call( this );
    this.name = "MovingGizmoPlane";

    this.moveFunc = null;
    
    this.dir = dir.clone();
    
    this.lookAt(dir);

    if ( hex === undefined ) hex = 0xffff00;

    var meshMaterial = new THREE.MeshBasicMaterial( {
        color: hex
    } );   
    
   meshMaterial.side = THREE.DoubleSide;
   
   var planeGeometry = new THREE.PlaneGeometry(4, 4);
   this.planeFront = new THREE.Mesh( planeGeometry, meshMaterial );
   this.planeBack = new THREE.Mesh( planeGeometry, meshMaterial );
   this.planeFront.position.set( 0, 0, 2 );
   this.planeBack.position.set( 0, 0, -2); 
   this.add( this.planeFront );
   this.add( this.planeBack );
   
   this.planeFront.pickable = true;
   this.planeBack.pickable = true;
   this.planeFront.pickRef = this;
   this.planeBack.pickRef = this;
};

OSMEX.MovingGizmoPlane.prototype = Object.create( THREE.Object3D.prototype );

OSMEX.MovingGizmoPlane.prototype.setPosition = function ( position ) {
    
    if (this.moveFunc) {
        
        this.moveFunc(position);
    }

};

OSMEX.MovingGizmoPlane.prototype.setColor = function ( hex ) {
    
    this.planeFront.material.color.setHex( hex );
    this.planeBack.material.color.setHex( hex );
}; 
