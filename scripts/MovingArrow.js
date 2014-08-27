var OSMEX = OSMEX || { REVISION: '1' };

OSMEX.MovingArrow = function ( dir, hex ) {
    
    OSMEX.Arrow.call( this, dir, new THREE.Vector3( 0, 0, 0 ), 30, hex, "moving" );
    this.name = "MovingArrow";            

    this.cone.pickable = true;
    this.cone.pickRef = this;

    this.moveFunc = null;
};

OSMEX.MovingArrow.prototype = Object.create( OSMEX.Arrow.prototype );

OSMEX.MovingArrow.prototype.setPosition = function ( position ) {
    
    if (this.moveFunc) {
  
        this.moveFunc(position.multiply(this.dir)); 
    }
    
};
