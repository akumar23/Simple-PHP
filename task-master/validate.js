function validateForm(form){
  fail = validateEmail(form.email.value)
  fail += validateUser(form.user.value)
  fail += validatePass(form.pass.value)

  if(fail == ""){
    return true
  } else {
    alert(fail);
    return false;
  }

}

function validateEmail(field){
  return(field == "")?"No emai was entered\n":""
}

function validateUser(field){
  return(field == "")?"No username was entered\n":""
}

function validatePass(field){
  if(field == "") return "No username was entered\n"
  else if(field.length < 4) return "Passwords must be at least 4 characters\n"
  return ""
}
