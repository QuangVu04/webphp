// Modal Image Gallery
function onClick(element) {
  document.getElementById("img01").src = element.src;
  document.getElementById("modal01").style.display = "block";
  var captionText = document.getElementById("caption");
  captionText.innerHTML = element.alt;
}

// Toggle between showing and hiding the sidebar when clicking the menu icon
function w3_open() {
  var mySidebar = document.getElementById("mySidebar");
  if (mySidebar.style.display === "block") {
    mySidebar.style.display = "none";
  } else {
    mySidebar.style.display = "block";
  }
}

function w3_close() {
  var mySidebar = document.getElementById("mySidebar");
  mySidebar.style.display = "none";
}

function validEmail(email) {
  if (!email) {
    return false;
  }
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email.trim());
}

function readInput(formName, inputName) {
  return document.forms[formName][inputName].value;
}

function validateForm(e) {
  e.preventDefault();
  var name = readInput("myForm", "Name");
  var email = readInput("myForm", "Email");
  var subject = readInput("myForm", "Subject");
  var message = readInput("myForm", "Message");

  if (!name) {
    alert("Vui lòng nhập họ và tên.");
    return false;
  }

  if (!subject) {
    alert("Vui lòng nhập chủ đề.");
    return false;
  }

  if (!validEmail(email)) {
    alert("Email không hợp lệ (VD: x@gmail.com).");
    isValid = false;
  }

  if (!message) {
    alert("Vui lòng nhập nội dung tin nhắn.");
    return false;
  }

  isValid = true;

  if (isValid) {
    alert("Liên lạc thành công");
  }
}
