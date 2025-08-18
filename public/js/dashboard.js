const API_URL = "http://localhost:8000/api/users";
window.currentSearchContext = null;

/* --- Render bảng users + pagination --- */
function renderUsersTable(response) {
  const tbody = document.querySelector("#userTable tbody");
  tbody.innerHTML = "";

  const users = response.data;
  users.forEach((user) => {
    const row = document.createElement("tr");
    let statusColor = "";
    if (user.status === "active") {
      statusColor =
        'style="color: green; display:inline-flex; background-color: #d4edda; padding: 5px;border-radius: 10px;"';
    } else if (user.status === "inactive") {
      statusColor =
        'style="color: red; display:inline-flex; justify-content: center; text-align: center; background-color: #f8d7da;padding: 5px; border-radius: 10px;"';
    }
    row.innerHTML = `
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                    <td>${user.full_name || ""}</td>
                    <td>${user.phone_number || ""}</td>
                    <td ${statusColor}>${user.status || ""}</td>
                    <td>${user.role || ""}</td>
                    <td class= "flex" style="align-items: center; gap: 10px;">
                        <i class = "fa fa-edit" onclick="editUser('${user.id}')"></i>
                        <i class = "fa fa-trash-o" onclick="deleteUser('${
                          user.id
                        }')"></i>
                    </td>
                `;
    tbody.appendChild(row);
  });

  renderPagination(response.page, response.total_pages);
}

function renderPagination(currentPage, totalPages) {
  const pagination = document.getElementById("pagination");
  pagination.innerHTML = "";

  // Previous
  if (currentPage > 1) {
    const prevBtn = document.createElement("button");
    prevBtn.textContent = "←";
    prevBtn.addEventListener("click", () => {
      if (window.currentSearchContext) {
        window.currentSearchContext.page = currentPage - 1;
        searchUsers(window.currentSearchContext);
      } else {
        fetchUsers(currentPage - 1);
      }
    });
    pagination.appendChild(prevBtn);
  }

  // Chỉ hiển thị các trang gần currentPage
  const range = 1; // số trang trước/sau currentPage muốn hiển thị
  const start = Math.max(1, currentPage - range);
  const end = Math.min(totalPages, currentPage + range);

  for (let i = start; i <= end; i++) {
    const btn = document.createElement("button");
    btn.textContent = i;
    if (i === currentPage) btn.disabled = true;
    btn.addEventListener("click", () => {
      if (window.currentSearchContext) {
        window.currentSearchContext.page = i;
        searchUsers(window.currentSearchContext);
      } else {
        fetchUsers(i);
      }
    });
    pagination.appendChild(btn);
  }

  // Next
  if (currentPage < totalPages) {
    const nextBtn = document.createElement("button");
    nextBtn.textContent = "→";
    nextBtn.addEventListener("click", () => {
      if (window.currentSearchContext) {
        window.currentSearchContext.page = currentPage + 1;
        searchUsers(window.currentSearchContext);
      } else {
        fetchUsers(currentPage + 1);
      }
    });
    pagination.appendChild(nextBtn);
  }
}

/* --- Fetch all users (GET) --- */
async function fetchUsers(page = 1, limit = 5) {
  try {
    const token = TokenManager.getAccessToken();
    const res = await fetch(`${API_URL}?page=${page}&limit=${limit}`, {
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    if (!res.ok) {
      if (res.status === 401) {
        TokenManager.clearTokens();
        window.location.replace("login.html");
      }
      throw new Error(`HTTP error ${res.status}`);
    }
    const data = await res.json();
    window.currentSearchContext = null;
    renderUsersTable(data);
  } catch (err) {
    console.error(err);
  }
}

function editUser(id) {
  document.getElementById("userModal").style.display = "flex";
  document.getElementById("userId").value = id;
}


/* --- Search users (POST) --- */
async function searchUsers(context) {
  try {
    const token = TokenManager.getAccessToken();

    window.currentSearchContext = context;
    const res = await fetch(`${API_URL}/search`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(context),
    });
    const data = await res.json();
    renderUsersTable(data);
  } catch (err) {
    console.error(err);
  }
}

/* --- Create / Update / Delete --- */
async function createUser(user) {
  const res = await fetch(API_URL, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(user),
  });
  return res.json();
}

async function updateUser(id, user) {
  const token = TokenManager.getAccessToken();

  try {
    const res = await fetch(`${API_URL}/${id}`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(user),
    });

    if (!res.ok) {
      const errData = await res.json().catch(() => ({}));
      throw new Error(errData.message || "Update user failed");
    }

    return await res.json();
  } catch (error) {
    console.error("UpdateUser error:", error);
    throw error;
  }
}

async function deleteUser(id) {
  try {
    console.log("Deleting user with ID:", id);
    if (!confirm("Bạn có chắc muốn xóa user này?")) return;
    const token = TokenManager.getAccessToken();
    const res = await fetch(`${API_URL}/${id}`, {
      method: "DELETE",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    if (res.status === 204) {
      alert("Đã xóa!");
      if (window.currentSearchContext) searchUsers(window.currentSearchContext);
      else fetchUsers();
    }
  } catch (err) {
    const errMsg = await res.json();
    alert("Lỗi: " + errMsg.error);
  }
}

/* --- Edit / Reset Form --- */

function closeModal() {
  document.getElementById("userModal").style.display = "none";
  resetForm();
}

function resetForm() {
  document.getElementById("userId").value = "";
  document.getElementById("userForm").reset();
}

function resetSearch() {
  document.getElementById("searchForm").reset();
  window.currentSearchContext = null;
  fetchUsers();
}

/* --- Event submit searchForm --- */
document.getElementById("searchForm").addEventListener("submit", (e) => {
  e.preventDefault();
  const context = {
    username: document.getElementById("searchUsername").value,
    email: document.getElementById("searchEmail").value,
    phone: document.getElementById("searchPhone").value,
    role: document.getElementById("searchRole").value,
    status: document.getElementById("searchStatus").value,
    fromDate: document.getElementById("searchFromDate").value,
    toDate: document.getElementById("searchToDate").value,
    page: 1,
    limit: 5,
  };
  searchUsers(context);
});

/* --- Init --- */
fetchUsers();
