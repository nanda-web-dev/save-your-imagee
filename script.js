import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import {
  getDatabase,
  ref,
  push,
  set,
  get
} from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";
import {
  getAuth,
  createUserWithEmailAndPassword,
  signInWithEmailAndPassword,
  onAuthStateChanged,
  signOut
} from "https://www.gstatic.com/firebasejs/10.12.2/firebase-auth.js";

const firebaseConfig = {
  apiKey: "AIzaSyCMcdj6ZkSJZACXaBMySkJ_m2lRMgDu8fM",
  authDomain: "penyimpan-foto.firebaseapp.com",
  databaseURL: "https://penyimpan-foto-default-rtdb.firebaseio.com",
  projectId: "penyimpan-foto",
  storageBucket: "penyimpan-foto.firebasestorage.app",
  messagingSenderId: "629992687510",
  appId: "1:629992687510:web:19b0ce0f32794b3b3b7fb7"
};

const app = initializeApp(firebaseConfig);
const db = getDatabase(app);
const auth = getAuth(app);

// DOM elements
const authContainer = document.getElementById("authContainer");
const appDashboard = document.getElementById("appDashboard");
const userDisplaySpan = document.getElementById("userDisplayEmail");
const galleryGrid = document.getElementById("galleryGrid");
const loginEmail = document.getElementById("loginEmail");
const loginPassword = document.getElementById("loginPassword");
const signupBtn = document.getElementById("signupBtn");
const signinBtn = document.getElementById("signinBtn");
const logoutBtn = document.getElementById("logoutBtnNew");
const galaxyFile = document.getElementById("galaxyFile");
const uploadCosmicBtn = document.getElementById("uploadCosmicBtn");
const dropConstellation = document.getElementById("dropConstellation");
const filePreviewName = document.getElementById("filePreviewName");

let currentUser = null;

// Helper loading state for auth
function setAuthLoading(loading, isSignup) {
  if (isSignup) {
    signupBtn.disabled = loading;
    signupBtn.textContent = loading ? "⏳ memproses..." : "⟡ DAFTAR ⟡";
  } else {
    signinBtn.disabled = loading;
    signinBtn.textContent = loading ? "⏳ memproses..." : "✦ MASUK ✦";
  }
}

// Upload loader
let isUploading = false;
function setUploadLoading(loading) {
  isUploading = loading;
  uploadCosmicBtn.disabled = loading;
  uploadCosmicBtn.innerHTML = loading
    ? '<span>🚀 mengunggah ...</span>'
    : '<span>📡 unggah ke ruang angkasa</span>';
}

// Signup logic
signupBtn.addEventListener("click", async () => {
  const email = loginEmail.value.trim();
  const password = loginPassword.value;
  if (!email || !password) {
    alert("🌌 Isi email & password terlebih dahulu!");
    return;
  }
  setAuthLoading(true, true);
  try {
    await createUserWithEmailAndPassword(auth, email, password);
    alert("🎉 Selamat! Akun kosmik berhasil dibuat.");
  } catch (error) {
    if (error.code === "auth/email-already-in-use") alert("⚠️ Email sudah terdaftar di galaksi lain!");
    else if (error.code === "auth/weak-password") alert("🔐 Password minimal 6 karakter!");
    else alert("❌ " + error.message);
  } finally {
    setAuthLoading(false, true);
  }
});

// Signin logic
signinBtn.addEventListener("click", async () => {
  const email = loginEmail.value.trim();
  const password = loginPassword.value;
  if (!email || !password) {
    alert("Masukkan kredensial untuk masuk ke vault.");
    return;
  }
  setAuthLoading(false, false);
  try {
    await signInWithEmailAndPassword(auth, email, password);
    alert("✨ Akses granted. Menuju galeri...");
  } catch (error) {
    if (error.code === "auth/invalid-credential") alert("❌ Email atau password salah!");
    else alert(error.message);
  } finally {
    setAuthLoading(false, false);
  }
});

// Logout
logoutBtn.addEventListener("click", async () => {
  await signOut(auth);
});

// Upload Foto ke Firebase RTDB (base64)
async function uploadFoto(file) {
  if (!currentUser) return;
  if (!file) {
    alert("Pilih foto dulu ya!");
    return;
  }
  if (!file.type.startsWith("image/")) {
    alert("Hanya gambar yang diperbolehkan!");
    return;
  }
  if (file.size > 5 * 1024 * 1024) {
    alert("Ukuran maksimal 5MB!");
    return;
  }
  setUploadLoading(true);
  const reader = new FileReader();
  reader.onload = async function (ev) {
    const base64 = ev.target.result;
    const safeEmail = currentUser.email.replace(/\./g, "_");
    try {
      const fotoRef = push(ref(db, `users/${safeEmail}/foto`));
      await set(fotoRef, {
        gambar: base64,
        waktu: Date.now(),
        nama: file.name
      });
      alert("✅ Foto berhasil diunggah ke kosmos!");
      galaxyFile.value = "";
      if (filePreviewName) filePreviewName.innerText = "";
      await renderGallery();
    } catch (err) {
      console.error(err);
      alert("Gagal mengunggah, coba lagi.");
    } finally {
      setUploadLoading(false);
    }
  };
  reader.onerror = () => {
    alert("Gagal membaca file.");
    setUploadLoading(false);
  };
  reader.readAsDataURL(file);
}

uploadCosmicBtn.addEventListener("click", () => {
  const file = galaxyFile.files[0];
  uploadFoto(file);
});

galaxyFile.addEventListener("change", (e) => {
  if (e.target.files.length) {
    filePreviewName.innerText = `📎 ${e.target.files[0].name}`;
  } else {
    filePreviewName.innerText = "";
  }
});

// Drag & Drop
dropConstellation.addEventListener("click", () => galaxyFile.click());
dropConstellation.addEventListener("dragover", (e) => {
  e.preventDefault();
  dropConstellation.style.borderColor = "#c084fc";
  dropConstellation.style.background = "rgba(139, 92, 246, 0.2)";
});
dropConstellation.addEventListener("dragleave", () => {
  dropConstellation.style.borderColor = "#7c3aed";
  dropConstellation.style.background = "rgba(79, 70, 229, 0.05)";
});
dropConstellation.addEventListener("drop", (e) => {
  e.preventDefault();
  dropConstellation.style.borderColor = "#7c3aed";
  const files = e.dataTransfer.files;
  if (files.length) {
    galaxyFile.files = files;
    filePreviewName.innerText = `📎 ${files[0].name}`;
  }
});

// Render Gallery
async function renderGallery() {
  if (!currentUser) return;
  galleryGrid.innerHTML = `<div class="loader-cosmic">🌀 memuat foto-foto nebula...</div>`;
  const safeEmail = currentUser.email.replace(/\./g, "_");
  try {
    const snapshot = await get(ref(db, `users/${safeEmail}/foto`));
    galleryGrid.innerHTML = "";
    if (snapshot.exists()) {
      const data = snapshot.val();
      const entries = Object.entries(data).reverse();
      if (entries.length === 0) {
        galleryGrid.innerHTML = `<div class="empty-gallery-msg">🌙 Belum ada foto, unggah karya pertamamu! ✨</div>`;
        return;
      }
      entries.forEach(([id, item], idx) => {
        const card = document.createElement("div");
        card.className = "card-cosmic";
        card.innerHTML = `
          <img src="${item.gambar}" alt="visual" loading="lazy">
          <div class="card-content">
            <span class="image-title">📸 artefak #${entries.length - idx}</span>
            <a href="${item.gambar}" download="cosmic-${Date.now()}-${idx}.jpg" class="download-cosmic">⬇ unduh</a>
          </div>
        `;
        galleryGrid.appendChild(card);
      });
    } else {
      galleryGrid.innerHTML = `<div class="empty-gallery-msg">🌠 Galeri kosong. Upload foto pertama! 🌠</div>`;
    }
  } catch (err) {
    console.error(err);
    galleryGrid.innerHTML = `<div class="empty-gallery-msg">⚠️ Gagal memuat galeri.</div>`;
  }
}

// Auth State Listener
onAuthStateChanged(auth, async (user) => {
  if (user) {
    currentUser = user;
    authContainer.style.display = "none";
    appDashboard.style.display = "block";
    userDisplaySpan.innerText = user.email;
    await renderGallery();
  } else {
    currentUser = null;
    authContainer.style.display = "flex";
    appDashboard.style.display = "none";
    loginEmail.value = "";
    loginPassword.value = "";
    galleryGrid.innerHTML = "";
    if (filePreviewName) filePreviewName.innerText = "";
    galaxyFile.value = "";
  }
});