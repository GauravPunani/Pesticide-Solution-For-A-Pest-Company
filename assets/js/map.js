if (navigator.geolocation) {
    let options = {
        enableHighAccuracy:true,
        maximumAge:0                
    };
    geoLoc = navigator.geolocation;
    
    navigator.geolocation.getCurrentPosition(save_cords,errorHandler, options);

    setTimeout(function(){
        navigator.geolocation.getCurrentPosition(save_cords,errorHandler, options);
    }, 5000*60);
}

function errorHandler(err) {
    if(err.code == 1) {
        console.log('Error: Access is denied!');
    } else if( err.code == 2) {
        console.log('Error: Position is unavailable!');
    }
}

function save_cords(position){

    console.log('fetching position');
    console.log(position);

    let last_postion = localStorage.getItem("last_coordinates");

    console.log('last cordinates'+last_postion);
    console.log('new cords are '+position.coords.latitude+" "+position.coords.longitude);

    let same_cords=false;

    if(last_postion!="" && last_postion!=null){
        last_postion=JSON.parse(last_postion);

        if(last_postion.lat==position.coords.latitude && last_postion.lng==position.coords.longitude){
            same_cords=true;
            console.log(`cords are same ,don't save it`);
        }
    }

    if(!same_cords){
        let data={
            lat:position.coords.latitude,
            lng:position.coords.longitude        
        };

        console.log('saving data in database');
        localStorage.setItem("last_coordinates",JSON.stringify(data));

        jQuery.ajax({
            type:"post",
            url:my_ajax_object.ajax_url,
            data:{
                action:"save_cordinates",
                lat:position.coords.latitude,
                lng:position.coords.longitude,
                "_wpnonce": my_ajax_object.nonce
            },
            dataType:"json",
            success:function(data){
                console.log(data);
            }
        });
    }
    else{
        console.log('coord found were same as they were in previous request');
    }
}