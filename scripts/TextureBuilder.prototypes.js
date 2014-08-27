function jsPoint(x,y)
{
    this.x=x;
    this.y=y;
    return this;
}
function Region()
{
    
}
function RectangleRegion(emptyPointArray,index)
{
   this.name="Unnamed "+(PolygonRegion.prototype.regions.length+1);
   this.points=emptyPointArray;
   this.regionItem=null;
   this.completed=true;
   this.index=PolygonRegion.prototype.regions.length;
   PolygonRegion.prototype.regions.push(this);
}

function PolygonRegion(emptyPointArray,index)
{
    // specific properties for each PolygonRegion object
    this.name="Unnamed "+(PolygonRegion.prototype.regions.length+1);
    this.completed=false;
    this.points=emptyPointArray;
    this.pointsShadowCopy=[];
    this.oldPoint=new jsPoint(0,0);
    this.regionItem=null;
    this.index=PolygonRegion.prototype.regions.length;
    PolygonRegion.prototype.regions.push(this);
    return this;
}

function extend(Child, Parent) {
    var F = function() { }
    F.prototype = Parent.prototype
    Child.prototype = new F()
    Child.prototype.constructor = Child
    Child.superclass = Parent.prototype
}

extend(PolygonRegion, Region);
extend(RectangleRegion, Region);

Region.prototype.attachRegionItem=function(template)
{
    this.regionItem=$(template);
    var r=this;
    this.regionItem.find('button')
    .button({
        icons: 
            {
                primary: "ui-icon-circle-close"
            },
        text: false
    });
    this.regionItem.find('input').val(this.name);
    this.regionItem.find('input').on('input',function(e){
        r.name=r.regionItem.find('input')[0].value;
    });
    this.regionItem.click(function(){
        r.onselect.call(r,r.index);
    });
    var button=this.regionItem.find('button');
    button.click(function(){
        r.onremove.call(r,r.index);
    });
}
Region.prototype.onremove=function(){};
Region.prototype.onselect=function(){};
Region.prototype.select=function(func)
{
    this.onselect=func;
};
Region.prototype.remove=function(func)
{
    this.onremove=func;
};

Region.prototype.regions=[];
Region.prototype.unset=function()
{
    Region.prototype.regions.splice(this.index,1);
    for(var i=0;i<Region.prototype.regions.length;i++)
        Region.prototype.regions[i].index=i;
}

RectangleRegion.prototype.completed=true;
RectangleRegion.prototype.type="rectangle";
RectangleRegion.prototype.handlesContainer=null;
RectangleRegion.prototype.jcropInstance=null;
RectangleRegion.prototype.onjcropselect=function(selection){
    if(!RectangleRegion.prototype.jcropInstance) return;
    var opt=RectangleRegion.prototype.jcropInstance.getOptions();
    var r=opt.region;
    r.points[0]=new jsPoint(selection.x,selection.y);
    r.points[1]=new jsPoint(selection.x2,selection.y);
    r.points[2]=new jsPoint(selection.x2,selection.y2);
    r.points[3]=new jsPoint(selection.x,selection.y2);
};
RectangleRegion.prototype.getRegion=function(sourceImage)
{
    var canvas=$('<canvas></canvas>')[0];
    var plainPoints=[];
    var g=canvas.getContext("2d");
    var x=this.points[0].x;
    var y=this.points[0].y;
    var width=this.points[1].x-this.points[0].x;
    var height=this.points[3].y-this.points[0].y;
    canvas.width=width;
    canvas.height=height;
    g.drawImage(sourceImage, x, y, width, height, 0, 0, width, height);
    for(var i=0;i<this.points.length;i++)
        plainPoints.push(this.points[i].x-this.points[0].x,this.points[i].y-this.points[0].y);
    var url=canvas.toDataURL();
    var result={
        name:this.name,
        points:plainPoints,
        dataurl:url
    };
    return result;
}
RectangleRegion.prototype.attachJcrop=function(jcrop)
{
    var r=this;
    var handler=RectangleRegion.prototype.onjcropselect;
    if(!RectangleRegion.prototype.jcropInstance) RectangleRegion.prototype.jcropInstance=jcrop;
    var bounds=RectangleRegion.prototype.jcropInstance.getBounds();
    var options={
            region:r,
            bgColor:'black',
            setSelect:[ bounds[0]/2-25, bounds[1]/2+25, bounds[0]/2+25, bounds[1]/2-25 ],
            allowSelect: false,
            minSize: [ 5, 5 ],
            onSelect:handler
        }
    RectangleRegion.prototype.jcropInstance.setOptions(options);
}
RectangleRegion.prototype.redraw=function()
{
    var x=this.points[0].x;
    var y=this.points[0].y;
    var x2=this.points[2].x;
    var y2=this.points[2].y;
    var handler=RectangleRegion.prototype.onjcropselect;
    var r=this;
    var options={
            region:r,
            bgColor:'black',
            setSelect:[ x, y, x2, y2 ],
            allowSelect: false,
            minSize: [ 5, 5 ],
            onSelect:handler
        }
     RectangleRegion.prototype.jcropInstance.setOptions(options);
    //RectangleRegion.prototype.jcropInstance.setSelect([x,y,x2,y2]);
    /*
    var r=this;
    var options={
        bgColor:'black',
        setSelect:[ x, y, x2, y2 ],
        allowSelect: false,
        minSize: [ 5, 5 ],
        onSelect:function(selection)
        {
            r.selectionHandler.call(r,selection);
        }
    }
    this.handlesContainer.Jcrop(options,function(){
        if(r.jcropInstance)
        {
            r.jcropInstance=this;
        }
    });*/
}
RectangleRegion.prototype.hide=function()
{
    RectangleRegion.prototype.jcropInstance.release();
    /*this.jcropInstance.destroy();
    this.jcropInstance=null;*/
};
RectangleRegion.prototype.clear=function()
{
    
}

// Common properties and methods for each PolygonRegion object
//PolygonRegion.prototype = 
//{
PolygonRegion.prototype.type="polygon";
PolygonRegion.prototype.canvas=null;
PolygonRegion.prototype.handlesContainer=null;
PolygonRegion.prototype.image=null;
PolygonRegion.prototype.onstatusChanged=function(){};
PolygonRegion.prototype.onerror=function(){};
PolygonRegion.prototype.onhideMessage=function(){};
PolygonRegion.prototype.onremove=function(){};
PolygonRegion.prototype.onselect=function(){};
PolygonRegion.prototype.getRegion=function(sourceImage)
{
    var topleft=new jsPoint(sourceImage.width, sourceImage.height);
    var bottomright=new jsPoint(0,0);
    var points=this.points;
    var plainPoints=[];
    for(var i=0;i<points.length;i++)
    {
        if(points[i].x<=topleft.x)
            topleft.x=points[i].x;
        if(points[i].y<=topleft.y)
            topleft.y=points[i].y;
        if(points[i].x>bottomright.x)
            bottomright.x=points[i].x;
        if(points[i].y>bottomright.y)
            bottomright.y=points[i].y;
    }
    var boundingRect={x:topleft.x,y:topleft.y,width:bottomright.x-topleft.x,height:bottomright.y-topleft.y};
    var canvas=$('<canvas></canvas>')[0];
    canvas.width=boundingRect.width;
    canvas.height=boundingRect.height;
    var g=canvas.getContext("2d");
    g.drawImage(sourceImage, boundingRect.x, boundingRect.y, boundingRect.width, boundingRect.height, 0, 0, boundingRect.width, boundingRect.height);
    g.globalCompositeOperation='destination-in';
    //var url=canvas.toDataURL();
    //var image=$("<img src='"+url+"'/>");
    //var mask=$('<canvas></canvas>')[0];
    //mask.width=boundingRect.width;
    //mask.height=boundingRect.height;
    //var context=mask.getContext("2d");
    g.beginPath();
    g.moveTo(points[0].x-boundingRect.x,points[0].y-boundingRect.y);
    var x,y;
    for(var i=0;i<points.length;i++)
    {
        //x[i]-=boundingRect.x;
        //y[i]-=boundingRect.y;
        x=points[i].x-boundingRect.x;
        y=points[i].y-boundingRect.y;
        if(i==0)
            g.moveTo(x,y);
        g.lineTo(x,y);
        plainPoints.push(x, y);
    }
    g.lineTo(points[0].x-boundingRect.x,points[0].y-boundingRect.y);
    g.closePath();
    g.fillStyle="black";
    g.fill();
    var url=canvas.toDataURL();
    var result={
        name:this.name,
        points:plainPoints,
        dataurl:url
    };
    //var image=$("<img src='"+url+"'/>");
    return result;
}
PolygonRegion.prototype.select=function(func)
{
    this.onselect=func;
};
PolygonRegion.prototype.remove=function(func)
{
    this.onremove=func;
};
PolygonRegion.prototype.attachCanvas=function(canvasObject)
{
  this.canvas=canvasObject;  
};
PolygonRegion.prototype.attachHandlesContainer=function(handlesContainer)
{
    this.handlesContainer=handlesContainer;
};
PolygonRegion.prototype.attachImage=function(imageObject)
{
    this.image=imageObject;
};
PolygonRegion.prototype.markFirstHandleCompleted=function()
{
     this.handlesContainer.find('.point:first').css({
        'background-color':'red'
    });
    this.completed=true;
    this.onstatusChanged.call(this, this.index);
};
PolygonRegion.prototype.isIntersected=function(c,d)
{
    if(this.points.length<=2) return false;
    for(var i=0;i<this.points.length;i++)
    {
        var a=this.points[i];
        var ni=0;
        if(i+1==this.points.length)
        {
            if(this.completed)
                ni=0;
            else
                return false;
        }
        else
            ni=i+1;
        var b=this.points[ni];     
        var common = (b.x - a.x)*(d.y - c.y) - (b.y - a.y)*(d.x - c.x);

            if (common == 0) continue;

            var rH = (a.y - c.y)*(d.x - c.x) - (a.x - c.x)*(d.y - c.y);
            var sH = (a.y - c.y)*(b.x - a.x) - (a.x - c.x)*(b.y - a.y);

            var r = rH / common;
            var s = sH / common;

            if (r >= 0 && r <= 1 && s >= 0 && s <= 1)
            {
                var u=((d.x-c.x)*(a.y-c.y)-(d.y-c.y)*(a.x-c.x))/((d.y-c.y)*(b.x-a.x)-(d.x-c.x)*(b.y-a.y));
                var x=a.x+u*(b.x-a.x);
                var y=a.y+u*(b.y-a.y);
                if((x==b.x && y==b.y)||(x==a.x&&y==a.y)) continue;
                return true;
            }
            else
                continue;
    }
    return false;
};
PolygonRegion.prototype.hide=function()
{
    this.handlesContainer.find('.point').remove();
    var g=this.canvas.getContext("2d");
    g.clearRect(0,0,g.canvas.width,g.canvas.height);
    g.drawImage(this.image,0,0);
}
PolygonRegion.prototype.clear=function()
{
    this.points.length=0;
    this.handlesContainer.find('.point').remove();
    this.redraw(false);
};
PolygonRegion.prototype.redraw=function(withHandles)
{
    var g=this.canvas.getContext("2d");
    g.clearRect(0,0,g.canvas.width,g.canvas.height);
    g.drawImage(this.image,0,0);
    g.strokeStyle="blue";
    if(this.points.length==0) return;
    if(this.completed)
    {
        g.beginPath();
        g.moveTo(this.points[0].x,this.points[0].y)
        for(var i=1;i<this.points.length;i++)
            g.lineTo(this.points[i].x,this.points[i].y);
        g.closePath();
        g.stroke();
    }
    else
    {
            g.beginPath();
            g.moveTo(this.points[0].x,this.points[0].y)
            for(var i=1;i<this.points.length;i++)
                g.lineTo(this.points[i].x,this.points[i].y);
            g.stroke();
    }
    if(withHandles)
    {
        this.handlesContainer.find('.point').remove();
        for(var i=0;i<this.points.length;i++)
        {
            this.createHandle(this.points[i].x, this.points[i].y);
        }
        if(this.completed)
            this.markFirstHandleCompleted();
    }
};
PolygonRegion.prototype.markFirstHandle=function(handle)
{
    handle.unbind('mouseup');
    handle.css({'background-color':'yellow'});
    var r=this;
    handle.click(function(){
        if(!handle.hasClass("dragged") && !r.completed)
        { 
            if(r.isIntersected(r.points[0], r.points[r.points.length-1]))
            {
                r.onerror.call(r, msg_cant_connect)
                //event.stopPropagation();
                return false;
            }
            if(r.points.length<3)
            {
                    r.onerror.call(r, msg_cant_complete);
                    return false;
            }
            r.markFirstHandleCompleted();
            r.redraw(false);
        }
        handle.removeClass("dragged");    
        return false;
    });
    handle.mouseup(function(event){
        if(event.button==2)
        {
            if(r.completed)
            {
                r.completed=false; 
                handle.css({
                    'background-color':'yellow'
                });
                r.pointsShadowCopy.length=0;
                r.pointsShadowCopy=r.points.slice(0);
                r.onstatusChanged.call(r, r.index);
                r.redraw(false);
            }
            else
            {
                r.deletePoint(handle);
                var first=r.handlesContainer.find(".point:first");
                r.markFirstHandle(first);
            }
            return true;
        }
    });
};
PolygonRegion.prototype.createHandle=function(x,y)
{
    var r=this;
    var handle=$("<div class='point'></div>").appendTo(this.handlesContainer);
    handle[0].oncontextmenu=function(){return false;}
    if(this.handlesContainer.find('.point').length==1)
        this.markFirstHandle(handle);
    else
    {
        handle.click(function(){
            return false;
        });
        handle.mouseup(function(event){
            if(event.button==2)
            {
                r.deletePoint(handle);
            }
            handle.removeClass("dragged");
        });
    }
    handle.draggable({ 
            containment: ".jcrop-holder", 
            scroll: false ,
            start: function()
            {
                r.onhideMessage();
                var p=$(this);
                var index=p.index()-1;
                r.oldPoint=new jsPoint(r.points[index].x,r.points[index].y);
            },
            drag: function() {
                    var p=$(this);
                    r.handlesContainer.find(".dragged").removeClass("dragged");
                    p.addClass("dragged");
                    var index=p.index()-1;
                    delete r.points[index];
                    r.points[index]=new jsPoint(p.position().left-r.handlesContainer.position().left+3,p.position().top-r.handlesContainer.position().top+3);
                    r.redraw(false);
            },
            stop: function()
            {
                if(r.points.length<=2) return;
                var p=$(this);

                var index=p.index()-1;
                var completed=r.completed;
                var nindex1=(index-1<0&&completed)?r.points.length-1:index-1;
                var nindex2=(index+1>=r.points.length&&completed)?0:index+1;
                var intersected=false;
                var d=new jsPoint(r.points[index].x,r.points[index].y);
                try
                {
                    var c1=new jsPoint(r.points[nindex1].x,r.points[nindex1].y);
                    intersected=intersected|r.isIntersected(c1, d);
                }catch(e){}
                try
                {
                    var c2=new jsPoint(r.points[nindex2].x,r.points[nindex2].y);
                    intersected=intersected|r.isIntersected(c2, d);
                }catch(e){}
                if(intersected)
                {
                    delete r.points[index];
                    r.points[index]=r.oldPoint;
                    p.css({'top':r.oldPoint.y+r.handlesContainer.position().top-3, 'left':r.oldPoint.x+r.handlesContainer.position().left-3});
                    r.redraw(false);
                    r.onerror.call(r, msg_cant_move_point);
                }                        
            }
    });
    handle.css({'top':y+this.handlesContainer.position().top-3, 'left':x+this.handlesContainer.position().left-3});
    //handle.css({'top':y-3, 'left':x-3});
};
PolygonRegion.prototype.addPoint=function(x,y)
{
    if(this.completed) return;
    var point=new jsPoint(x,y);
    var r=this;
    if(this.points.length>1)
    {
        var c=new jsPoint(x,y);
        var d=new jsPoint(this.points[this.points.length-1].x,this.points[this.points.length-1].y);
        if(this.isIntersected(c,d)) 
        {
            this.onerror.call(this,msg_cant_add_point);
            return;
        }
    }
    this.points.push(point);
    this.createHandle(x, y);	
    this.redraw(false);
};
PolygonRegion.prototype.deletePoint=function(handle)
{
    if(this.points.length-1<3)
    {
        this.pointsShadowCopy=this.points.slice(0);
    }
    var deleteTarget=this.points[handle.index()-1];
    delete this.points[handle.index()-1];
    this.points.splice(handle.index()-1,1);
    var flag=false;
    for(var i=0;i<this.points.length-1;i++)
    {
            if(this.isIntersected(this.points[i], this.points[i+1]))
            {
                flag=true;
                break;
            }
    }
    if(!flag)
    {
        handle.remove();
        this.redraw(false);
        if(this.points.length<3)
        {
            this.markFirstHandle(this.handlesContainer.find('.point:first'));
            this.completed=false;
            this.onstatusChanged.call(this, this.index);
        }
    }
    else
    {
        this.onerror.call(this, msg_cant_remove);
        this.points.splice(handle.index()-1,0,deleteTarget);
    }
};
PolygonRegion.prototype.errorOccurred=function(func)
{
    this.onerror=func;
};
PolygonRegion.prototype.hideMessage=function(func)
{
    this.onhideMessage=func;
};
PolygonRegion.prototype.statusChanged=function(func)
{
    this.onstatusChanged=func;
};

PolygonRegion.prototype.revert=function()
{
    if(this.pointsShadowCopy.length==0)
    {
        this.onremove.call(this,this.index,true);
        return;
    }
    this.points.length=0;
    for(var i=0;i<this.pointsShadowCopy.length;i++)
        this.points.push(this.pointsShadowCopy[i]);
    this.pointsShadowCopy.length=0;
    this.completed=true;
}
//}