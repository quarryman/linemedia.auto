/* zoom any image
 * widget by IonDen
 * v 2.3
 * 02.06.2010
 * rev. 55
 * depends on jQuery 1.4
 * UTF-8
 */
 
(function($){
    var placeToCenter = false; // этот параметр отвечает за размещение картинки

    var bpOff = 0;
    var bpWidth = 0;
    var bpHeight = 0;
    var bpcLeft = 0;
    var bpcTop = 0;
    
    var zoomWidth = 0;
    var zoomHeight = 0;
    
    var width_array = new Array();
    var height_array = new Array();
    
    var screenWidth = 0;
    var screenHeight = 0;
    var screenScroll = 0;
    var scrLeft = 0;
    var scrTop = 0;
    
    var localTop = 0;
    var localLeft = 0;
    
    var zoomTrash = '<div id="bigGrey"></div>\r<div class="zoomShadow"><table><tr><td class="zs1"><b></b></td><td class="zh1"><b></b></td><td class="zs2"><b></b></td></tr><tr><td class="zv1"><b></b></td><td><div class="zoomBase"></div></td><td class="zv2"><b></b></td></tr><tr><td class="zs3"><b></b></td><td class="zh2"><b></b></td><td class="zs4"><b></b></td></tr></table></div>\r';
	var zoomX = '<a href="#" class="zoomX"></a>\r';

    var firstClick = true;
    var galNum = 0;
    var oldZoom = 0;
    var currentZoom = 0;
    var oldW = 0;
    var oldH = 0;
    var oldOf = 0;
    var clicked = false;
    var strashno = false;
    var allLoaded = false;
    var loadStatus = 0;
    
    $("a.zoom").live("click", function(event) {
        event.preventDefault();
        $(this).blur();
        $("a.zoomX").remove();
        $("select").addClass("selOff");
        
        bpOff = $(this).offset();
        bpWidth = $(this).find("img").width();
        bpHeight = $(this).find("img").height();
        if(bpWidth === null) {
            bpWidth = $(this).width();
            bpHeight = $(this).height();
        }
        bpcLeft = Math.round(bpOff.left + (bpWidth / 2));
        bpcTop = Math.round(bpOff.top + (bpHeight / 2));
        
        screenScroll = $(window).scrollTop();
        screenWidth = $("body").innerWidth();
        screenHeight = $(window).height();
        
        scrLeft = Math.round(screenWidth / 2);
        scrTop = Math.round(screenScroll + (screenHeight / 2));
        
        if(firstClick === true) {
			$("body").append(zoomTrash);
            galNum = $(".zoom").length;
            for(var i = 0; i < galNum; i++) {
                $(".zoom:eq(" + i + ")").addClass("myZoom" + i);
            }
            $("body").append('<div id="zoomLoading"></div>\r')
            $("#zoomLoading").css("left", bpcLeft - 11).css("top", bpcTop - 11);

            for(var i = 0; i < galNum; i++) {
                var zoomUrl = $(".myZoom" + i).attr("href");
                $("body").append('<img class="zoomPic" src="' + zoomUrl + "?" + Math.random()*10 +  '" id="zoomPic' + i + '" />\r')
            }
            
            currentZoom = $(this).attr("class");
            currentZoom = currentZoom.slice(currentZoom.indexOf("myZoom"));
            currentZoom = currentZoom.slice(6);
            oldZoom = currentZoom;
            
            for(var i = 0; i < galNum; i++) {
                $("#zoomPic" + i).load(function() {
                    loadStatus++;
                    var localId = $(this).attr("id").slice(7);
                    width_array[localId] = $(this).width();
                    height_array[localId] = $(this).height();
                });
            }
            
            var loading = setInterval(waitForPics, 50);
            firstClick = false;
        } else {
            oldZoom = currentZoom;
            currentZoom = $(this).attr("class");
            currentZoom = currentZoom.slice(currentZoom.indexOf("myZoom"));
            currentZoom = currentZoom.slice(6);
            showPic();
        }
        
        function waitForPics() {
            if(loadStatus === galNum) {
                $("#zoomLoading").remove();
                clearInterval(loading);
                showPic();
            }
        }
    });
    
    function showPic() {

        zoomWidth = width_array[currentZoom];
        zoomHeight = height_array[currentZoom];
        
        var ratio = zoomWidth / zoomHeight;
        
        //check size
        if(zoomHeight > screenHeight - 45) {
            zoomHeight = screenHeight - 45;
            zoomWidth = zoomHeight * ratio;
        } else if(zoomWidth > screenWidth - 50) {
            zoomWidth = screenWidth - 50;
            zoomHeight = zoomWidth / ratio;
        }
        
        oldW = $(".myZoom" + oldZoom + " img").width();
        oldH = $(".myZoom" + oldZoom + " img").height();
        oldOf = $(".myZoom" + oldZoom + " img").offset();
		
        if(oldW === null) {
            oldW = $(".myZoom" + oldZoom).width();
            oldH = $(".myZoom" + oldZoom).height();
            oldOf = $(".myZoom" + oldZoom).offset();
        }
        
        if(placeToCenter === false) {
            // in place

            //check place
            localTop = bpcTop - (zoomHeight / 2);
            if(localTop < screenScroll + 20) {localTop = screenScroll + 20};
            if(localTop + zoomHeight > screenScroll + screenHeight - 20) {localTop = screenScroll + screenHeight - zoomHeight - 20};
            localLeft = bpcLeft - (zoomWidth / 2);
            if(localLeft < 20) {localLeft = 20};
            if(localLeft > screenWidth - zoomWidth - 25) {localLeft = screenWidth - zoomWidth - 25};
            
            $("#zoomPic" + currentZoom).css("top", bpOff.top).css("left", bpOff.left).css("width", bpWidth).css("height", bpHeight);

            $("#zoomPic" + currentZoom).animate({
                width:zoomWidth,
                height:zoomHeight,
                left:localLeft,
                top:localTop
            }, 200, setShadow);
            
            if(currentZoom !== oldZoom && strashno === false) {
                removeShadow();
                $("#zoomPic" + oldZoom).animate({
                    width:oldW,
                    height:oldH,
                    left:oldOf.left,
                    top:oldOf.top
                }, 200, removeOld);
            }
            
            strashno = false;
        } else {
            // in center
            
            //check place
            localTop = (screenHeight / 2) - (zoomHeight / 2);
            localLeft = (screenWidth / 2) - (zoomWidth / 2);
            
            $("#bigGrey").addClass("bigGrey").height($("body > div").innerHeight());
            
            $("#zoomPic" + currentZoom).css("top", bpOff.top).css("left", bpOff.left).css("width", bpWidth).css("height", bpHeight);

            $("#zoomPic" + currentZoom).animate({
                width:zoomWidth,
                height:zoomHeight,
                left:localLeft,
                top:localTop + $(window).scrollTop()
            }, 200, setShadow);
            
            if(currentZoom !== oldZoom && strashno === false) {
                removeShadow();
                $("#zoomPic" + oldZoom).animate({
                    width:oldW,
                    height:oldH,
                    left:oldOf.left,
                    top:oldOf.top
                }, 200, removeOld);
            }
            
            strashno = false;
        }
    };
    
    function setShadow() {
        clicked = false;
        var zsLeft = localLeft - 6;
        var zsTop = localTop - 6;
        if(placeToCenter === false) {
	        $("div.zoomShadow").css("left", zsLeft).css("top", zsTop);
		} else {
	        $("div.zoomShadow").css("left", zsLeft).css("top", zsTop).css("position", "fixed");
		}
        $("div.zoomShadow div.zoomBase").width(zoomWidth - 8).height(zoomHeight - 8);
        
        for(var i = 0; i < galNum; i++) {
            if(i !== parseInt(currentZoom)) {
                $("#zoomPic" + i).css("top", -9999).css("left", -1000).css("width", width_array[i]).css("height", height_array[i]);
            }
        }
        
        $("#zoomPic" + currentZoom).click(function(event){
            $("a.zoomX").remove();
            hideCurrent();
        });
        
        $("body").append(zoomX);
        if(placeToCenter === false) {
			$("a.zoomX").css("left", localLeft + zoomWidth - 16).css("top", localTop - 17);
		} else {
			$("a.zoomX").css("left", localLeft + zoomWidth - 16).css("position", "fixed").css("top", localTop - 17);
		}
        
        $("a.zoomX").click(function(event){
            event.preventDefault();
            $("a.zoomX").css("position", "absolute").remove();
            hideCurrent();
        });
        $("select").addClass("selOff");
		
        if(placeToCenter === true) {
			$("#zoomPic" + currentZoom).css("position", "fixed").css("top", localTop);
		}
    }
    function removeShadow() {
        $("div.zoomShadow").css("top", -9999).css("left", -1000).css("position", "absolute");
    }
    
    function removeOld() {
        $("#zoomPic" + oldZoom).css("top", -9999).css("left", -1000).css("width", width_array[oldZoom]).css("height", height_array[oldZoom]).css("position", "absolute");
    }
    
    function hideCurrent() {
        if(clicked === false) {
            $("#bigGrey").removeClass("bigGrey").height(0);
            $("select").removeClass("selOff");
            oldZoom = currentZoom;

            oldWidth = width_array[oldZoom];
            oldHeight = height_array[oldZoom];
            
            oldW = $(".myZoom" + oldZoom + " img").width();
            oldH = $(".myZoom" + oldZoom + " img").height();
            oldOf = $(".myZoom" + oldZoom + " img").offset();
            if(oldW === null) {
                oldW = $(".myZoom" + oldZoom).width();
                oldH = $(".myZoom" + oldZoom).height();
                oldOf = $(".myZoom" + oldZoom).offset();
            }

            removeShadow();
			
			if(placeToCenter === false) {
				$("#zoomPic" + oldZoom).animate({
					width:oldW,
					height:oldH,
					left:oldOf.left,
					top:oldOf.top
				}, 200, removeOld);
			} else {
				$("#zoomPic" + oldZoom).animate({
					width:oldW,
					height:oldH,
					left:oldOf.left,
					top:oldOf.top - $(window).scrollTop()
				}, 200, removeOld);
			}
            
            strashno = true;
        }
        clicked = true;
    }
    
    $("#bigGrey").click(function() {
        $("a.zoomX").remove();
        hideCurrent();
    });
    
    $(window).keydown(function(event) {
        if(event.keyCode == '27') {
            $("a.zoomX").remove();
            hideCurrent();
        }
        if(event.keyCode == '39' && galNum > 1) {
            removeShadow();
            $("a.zoomX").remove();
            oldZoom = currentZoom;

            if(firstClick === false) {
                if(currentZoom < galNum - 1) {
                    currentZoom++;
                } else {
                    currentZoom = 0;
                }
            }

            bpOff = $(".myZoom" + currentZoom).offset()
            bpWidth = $(".myZoom" + currentZoom).find("img").width();
            bpHeight = $(".myZoom" + currentZoom).find("img").height();
            if(bpWidth === null) {
                bpWidth = $(".myZoom" + currentZoom).width();
                bpHeight = $(".myZoom" + currentZoom).height();
            }
            bpcLeft = Math.round(bpOff.left + (bpWidth / 2));
            bpcTop = Math.round(bpOff.top + (bpHeight / 2));
        
            showPic();
        }
        if(event.keyCode == '37' && galNum > 1) {
            removeShadow();
            $("a.zoomX").remove();
            oldZoom = currentZoom;

            if(firstClick === false) {
                if(currentZoom > 0) {
                    currentZoom--;
                } else {
                    currentZoom = galNum - 1;
                }
            }
            
            bpOff = $(".myZoom" + currentZoom).offset()
            bpWidth = $(".myZoom" + currentZoom).find("img").width();
            bpHeight = $(".myZoom" + currentZoom).find("img").height();
            if(bpWidth === null) {
                bpWidth = $(".myZoom" + currentZoom).width();
                bpHeight = $(".myZoom" + currentZoom).height();
            }
            bpcLeft = Math.round(bpOff.left + (bpWidth / 2));
            bpcTop = Math.round(bpOff.top + (bpHeight / 2));
            
            showPic();
        }
    });
	
	function clearCache() {
		firstClick = true;
		galNum = 0;
		oldZoom = 0;
		currentZoom = 0;
		oldW = 0;
		oldH = 0;
		oldOf = 0;
		clicked = false;
		strashno = false;
		allLoaded = false;
		loadStatus = 0;

		$("img.zoomPic").remove();
		$("a.zoomX").remove();
		removeShadow();
	}
})(jQuery);