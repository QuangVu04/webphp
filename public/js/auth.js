// API Base URL - Change this to your backend URL
const API_BASE = "http://localhost:8000/api";

// Token management
const TokenManager = {
  setTokens: (accessToken, refreshToken) => {
    const tokenData = {
      accessToken,
      refreshToken,
      timestamp: Date.now(),
    };
    sessionStorage.setItem("auth_tokens", JSON.stringify(tokenData));
    console.log("Tokens set:", tokenData);
  },

  getAccessToken: () => {
    const tokenData = JSON.parse(sessionStorage.getItem("auth_tokens") || "{}");
    return tokenData.accessToken || null;
  },

  getRefreshToken: () => {
    const tokenData = JSON.parse(sessionStorage.getItem("auth_tokens") || "{}");
    return tokenData.refreshToken || null;
  },

  clearTokens: () => {
    sessionStorage.removeItem("auth_tokens");
  },

  isLoggedIn: () => {
    return !!TokenManager.getAccessToken();
  },
};

// API Service
const ApiService = {
  makeRequest: async (endpoint, options = {}) => {
    const url = `${API_BASE}${endpoint}`;
    const config = {
      headers: {
        "Content-Type": "application/json",
        ...options.headers,
      },
      ...options,
    };

    // Add auth token if available
    const token = TokenManager.getAccessToken();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    try {
      const response = await fetch(url, config);
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || "Request failed");
      }

      return data;
    } catch (error) {
      console.error("API Error:", error);
      throw error;
    }
  },

  login: async (email, password) => {
    return ApiService.makeRequest("/auth/login", {
      method: "POST",
      body: JSON.stringify({
        email,
        password,
      }),
    });
  },

  register: async (userData) => {
    return ApiService.makeRequest("/auth/register", {
      method: "POST",
      body: JSON.stringify(userData),
    });
  },

  getCurrentUser: async () => {
    return ApiService.makeRequest("/auth/me");
  },

  logout: async () => {
    const token = TokenManager.getAccessToken();
    console.log("Logging out with token:", token);
    if (!token) throw new Error("No token found");

    return await ApiService.makeRequest("/auth/logout", {
      method: "POST",
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });
  },
};

// Tab switching
function switchTab(tabName) {
  // Update tab buttons
  document.querySelectorAll(".tab-button").forEach((btn) => {
    btn.classList.remove("active");
  });
  event.target.classList.add("active");

  // Update tab content
  document.querySelectorAll(".tab-content").forEach((content) => {
    content.classList.remove("active");
  });
  document.getElementById(tabName + "Tab").classList.add("active");

  // Clear previous alerts
  clearAlerts();
}

// Password toggle
function togglePassword(inputName) {
  const input = document.querySelector(`input[name="${inputName}"]`);
  const toggle = event.target;

  if (input.type === "password") {
    input.type = "text";
    toggle.textContent = "🙈";
  } else {
    input.type = "password";
    toggle.textContent = "👁️";
  }
}

// Form validation
function validateEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validateForm(form) {
  let isValid = true;
  const inputs = form.querySelectorAll(".form-input");

  inputs.forEach((input) => {
    const errorMsg = input.parentNode.querySelector(".error-message");
    input.classList.remove("error");

    if (errorMsg) errorMsg.style.display = "none"; // <--- check

    if (input.required && !input.value.trim()) {
      input.classList.add("error");
      if (errorMsg) errorMsg.style.display = "block";
      isValid = false;
    }

    if (input.type === "email" && input.value && !validateEmail(input.value)) {
      input.classList.add("error");
      if (errorMsg) errorMsg.style.display = "block";
      isValid = false;
    }

    if (input.name === "password" && input.value && input.value.length < 6) {
      input.classList.add("error");
      if (errorMsg) errorMsg.style.display = "block";
      isValid = false;
    }

    if (input.name === "confirm_password" && input.value) {
      const password = form.querySelector('input[name="password"]').value;
      if (input.value !== password) {
        input.classList.add("error");
        if (errorMsg) errorMsg.style.display = "block";
        isValid = false;
      }
    }
  });

  return isValid;
}

// Show alerts
function showAlert(type, message, alertId) {
  const alert = document.getElementById(alertId);
  alert.className = `alert ${type}`;
  alert.textContent = message;
  alert.style.display = "block";
}

function clearAlerts() {
  document.querySelectorAll(".alert").forEach((alert) => {
    alert.style.display = "none";
  });
}

// Loading state management
function setLoading(button, isLoading) {
  const loader = button.querySelector(".loader");
  const text = button.querySelector(".btn-text");

  if (isLoading) {
    loader.style.display = "inline-block";
    text.style.opacity = "0.7";
    button.disabled = true;
  } else {
    loader.style.display = "none";
    text.style.opacity = "1";
    button.disabled = false;
  }
}

// Login form handler
document.getElementById("loginForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  if (!validateForm(e.target)) return;

  const submitBtn = e.target.querySelector(".submit-btn");
  setLoading(submitBtn, true);
  clearAlerts();

  try {
    const formData = new FormData(e.target);
    const email = formData.get("email");
    const password = formData.get("password");

    const response = await ApiService.login(email, password);

    TokenManager.setTokens(response.access_token, response.refresh_token);
    window.location.href = "index.html";

    showAlert("success", "Login successful!", "loginAlert");
  } catch (error) {
    showAlert("error", error.message, "loginAlert");
  } finally {
    setLoading(submitBtn, false);
  }
});

// Check if user is already logged in
window.addEventListener("load", function () {
  if (window.authData && window.authData.accessToken) {
    // User is already logged in, redirect to dashboard
    window.location.href = "dashboard.html";
  }
});

// Register form handler
document
  .getElementById("registerForm")
  .addEventListener("submit", async (e) => {
    e.preventDefault();

    if (!validateForm(e.target)) return;

    const submitBtn = e.target.querySelector(".submit-btn");
    setLoading(submitBtn, true);
    clearAlerts();

    try {
      const formData = new FormData(e.target);
      const userData = {
        username: formData.get("username"),
        email: formData.get("email"),
        password: formData.get("password"),
        full_name: formData.get("full_name") || "",
        phone_number: formData.get("phone_number") || "",
      };

      const response = await ApiService.register(userData);

      // Store tokens
      TokenManager.setTokens(response.access_token, response.refresh_token);

      window.location.href = "index.html";

      showAlert("success", "Registration successful!", "registerAlert");
    } catch (error) {
      showAlert("error", error.message, "registerAlert");
    } finally {
      setLoading(submitBtn, false);
    }
  });

// Logout function
async function logout() {
  try {
    await ApiService.logout();
  } catch (error) {
    console.error("Logout error:", error);
  } finally {
    TokenManager.clearTokens();
    clearAlerts();
  }
}

// Check if user is already logged in on page load
window.addEventListener("load", async () => {
  if (TokenManager.isLoggedIn()) {
    try {
      window.location.replace("/dashboard.html");
    } catch (error) {
      // Token might be expired, clear it
      TokenManager.clearTokens();
    }
  }
});

function protectRoute(redirectUrl = "login.html") {
    if (!TokenManager.isLoggedIn()) {
        // Nếu chưa login → chuyển hướng về login page
        window.location.replace(redirectUrl);
    }
}
