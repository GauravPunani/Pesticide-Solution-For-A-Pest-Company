const initMap = (element_id, callback) => {
    let input = document.getElementById(element_id);
    const options = {
       componentRestrictions: {country: "us"},
    }

    let autoComplete = new google.maps.places.Autocomplete(input, options);

    callback(undefined, autoComplete);
}

const initMapNew = (element_id, callback) => {
    let input = document.getElementById(element_id);

    const autocomplete_address = document.getElementsByClassName('autocomplete_address');


    const options = {
        componentRestrictions: {country: "us"},
    }

    for(let i = 0; i < autocomplete_address.length; i++){
        console.log(autocomplete_address[i]);
        let autoComplete = new google.maps.places.Autocomplete(autocomplete_address[i], options);     
    }
}