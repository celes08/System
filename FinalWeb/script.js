// Tab switching for login/signup forms (UX only)
document.addEventListener("DOMContentLoaded", function () {
  const loginTab = document.getElementById("loginTab");
  const signupTab = document.getElementById("signupTab");
  const loginForm = document.getElementById("loginForm");
  const signupForm = document.getElementById("signupForm");

  if (loginTab && signupTab && loginForm && signupForm) {
    loginTab.addEventListener("click", function () {
      loginForm.style.display = "block";
      signupForm.style.display = "none";
      loginTab.classList.add("active");
      signupTab.classList.remove("active");
    });

    signupTab.addEventListener("click", function () {
      loginForm.style.display = "none";
      signupForm.style.display = "block";
      signupTab.classList.add("active");
      loginTab.classList.remove("active");
    });
  }
});