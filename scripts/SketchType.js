var OSMEX = OSMEX || { REVISION: '1' };

OSMEX.SketchType = function (name, category, geometry, scale) {

    this.name = name || "undef";
    this.category = category || "undef";
    this.geometry = geometry || new THREE.Geometry();
    this.scale = scale || new THREE.Vector3();
};

OSMEX.SketchType.prototype = {

    constructor: OSMEX.SketchType
};
