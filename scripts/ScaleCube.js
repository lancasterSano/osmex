var OSMEX = OSMEX || { REVISION: '1' };

OSMEX.ScaleCube = function (  ) {
    
    OSMEX.Cube.call( this );
    this.name = "ScaleCube";
    
    this.prevSize = 1 / 20;
	
    this.cube.pickable = true;
    this.cube.pickRef = this;
    
    this.sizeFunc = null;
};

OSMEX.ScaleCube.prototype = Object.create( OSMEX.Cube.prototype );

OSMEX.ScaleCube.prototype.setScale = function ( scale ) {
    
    if (this.sizeFunc) {
        
        var size = scale / 20;
        this.sizeFunc(size - this.prevSize);
        this.prevSize = size;
    }

};

OSMEX.ScaleCube.prototype.restoreDefaultScale = function ( ) {
    
    this.prevSize = 1 / 20;
    
};
