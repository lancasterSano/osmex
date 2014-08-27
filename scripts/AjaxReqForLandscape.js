function build_func(_tile_id,_minlon,_minlat,_maxlon,_maxlat){
    $.ajax({
        async:true,
        type:'GET',
        url:'server_scripts/getBuildings.php',
        cache: false,
		processData: true,
        headers: {
            'Content-Type': 'application/json'
        },
        data: {tile_id: _tile_id, minlon: _minlon, minlat: _minlat,maxlon: _maxlon,maxlat: _maxlat},
        dataType:'text',
        success:function(r)
        {
            div=document.getElementById('build');
	        div.innerHTML=r;
	        div.ongetdata(div.innerHTML);
        },
        error:function()
        {
            console.debug("Can't load buildings");
        }
        
    });
	
}

function land_func(_id){
    $.ajax({
        async:true,
        type:'GET',
        url:'server_scripts/get_land.php',
        cache: false,
		processData: true,
        headers: {
            'Content-Type': 'application/json'
        },
        data: {id: _id},
        dataType:'text',
        success:function(r)
        {
            div=document.getElementById('cont');
	        div.innerHTML=r;
	        div.ongetdata(div.innerHTML);
        },
        error:function()
        {
            console.debug("Can't load heights of tile");
        }
        
    });
		
}