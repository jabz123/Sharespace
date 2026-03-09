const params = new URLSearchParams(window.location.search);
const btn = document.getElementById("resendBtn");

let seconds = 0;

// if resend just happened
if(params.get("resent")){
    seconds = 60;
}

// backend cooldown
if(params.get("cooldown")){
    seconds = parseInt(params.get("cooldown"));
}

if(seconds > 0){

    btn.disabled = true;

    const interval = setInterval(function(){

        btn.innerText = "Resend verification email available in " + seconds + "s";

        seconds--;

        if(seconds < 0){

            clearInterval(interval);

            btn.disabled = false;
            btn.innerText = "Resend Verification Email";

        }

    },1000);

}