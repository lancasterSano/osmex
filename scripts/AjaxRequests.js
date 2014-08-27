function loadSketchTypeFromServer(_id, successCallback){

    $.ajax({
        async:true,
        type:'GET',
        url:'server_scripts/getCustomGeometry.php',
        cache: false,
	processData: true,
        headers: {
            'Content-Type': 'application/json'
        },
        data: {id:_id},
        dataType:'text',
        success: successCallback,
        error:function()
        {
            console.debug("Can't load geometry");
        }
        
    });
}

function getBuildings(_minlon,_minlat,_maxlon,_maxlat, successCallback){
    $.ajax({
        async:true,
        type:'GET',
        url:'server_scripts/getBuildings.php',
        cache: false,
        processData: true,
        headers: {
            'Content-Type': 'application/json'
        },
        data: {tile_id: 0, minlon: _minlon, minlat: _minlat,maxlon: _maxlon,maxlat: _maxlat},
        dataType:'text',
        success: successCallback,
        error:function()
        {
            console.debug("Can't load buildings");
        }
        
    });
	
}

function ajaxPostScene(array, osmArea) {
    for (var i = 0; i < array.length; i++) {
        var lonLatHeight = osmArea.XyzToLonLatHeight(array[i].position);
        $.ajax({
            type: "GET",
            url: "server_scripts/AddInstance.php",
            cache: false,
            data: {uid: array[i].id, scaleX: array[i].scale.x/1.5, scaleY: array[i].scale.y, scaleZ: array[i].scale.z/1.5, rotationX: array[i].rotation.x, rotationY: array[i].rotation.y, rotationZ: array[i].rotation.z, positionLat: lonLatHeight.latitude, positionLon: lonLatHeight.longitude, positionHeight: lonLatHeight.height, TypeID: array[i].TypeID, isDeleted: array[i].isDeleted},
            success: function(data) {
                //alert(data);
            },
            error:function() {
                console.debug("Can't save scene");
            }
        })
    }
}

function ajaxNewSketch(name, category, serializedGeometry, scale, image) {
    var result;
    $.ajax({
        type: "POST",
        async:false,
        url: "server_scripts/NewSketch.php",
        cache: false,
        data: {name: name.val(), category: category.val(), geometry: JSON.stringify(serializedGeometry), origScaleX: scale.x, origScaleY: scale.y, origScaleZ: scale.z, imageData: image},
        dataType:'text',
        success: function(data) {
            result=data;
        }
    });
    return result;
}

function ajaxCheckUniqueName (name) {
    var result;
    $.ajax({
        type: "POST",
        async:true,
        url: "server_scripts/checkUniqueName.php",
        cache: false,
        data: {name: name.val()},
        success: function(data) {
            result = data;
        }
    });
    return result;
}
