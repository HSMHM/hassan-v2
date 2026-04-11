# Hassan Almalki Personal Website

This is the codebase for Hassan Almalki's personal portfolio website, built with **Vue 3** and **Vite** using **Static Site Generation (SSG)**. It features a bilingual (Arabic/English) interface, an animated UI, and a secure contact form with Google turnstile.

---

## 🌐 Technologies Used

- **Vue 3**
- **Vite + vite-ssg** (for static site generation)
- **Vue-i18n** (localization in Arabic and English)
- **GSAP** (for scroll and intro animations)
- **Mouse-follower.js** (custom cursor animation)
- **Express.js** (backend for contact form and emailing)
- **Firebase Hosting** (for deployment)

---

## ⚙️ Prerequisites

- Node.js `v16+`
- npm or yarn
- Firebase CLI

---

## 📦 Installation

1. **Clone the repository:**

   ```bash
   git clone https://github.com/yourusername/hassan-v2.git
   cd hassan-v2
   ```

2. **Install dependencies:**

   ```bash
   npm install
   ```

3. **Create environment files:**

   Copy the `.env.example` file to create the necessary environment files:

   ```bash
   cp .env.example .env
   cp .env.example .env.development
   cp .env.example .env.production
   ```

---

## 🔐 Environment Configuration

Update your environment files (`.env.development` and `.env.production`) with the correct values:

```env
GMAIL_USER=your_gmail@gmail.com
GMAIL_PASS=your_app_password
TO_EMAIL=recipient@example.com
NODE_ENV=development
NODE_TLS_REJECT_UNAUTHORIZED=0
VITE_TURNSTILE_SECRET=your_recaptcha_secret
VITE_TURNSTILE_SITE_KEY=your_recaptcha_site_key
VITE_TURNSTILE_API=https://www.google.com/recaptcha/api/siteverify
```

> ⚠️ **Important:** Never commit `.env` files to Git. Ensure they're listed in `.gitignore`.

---

## 🚀 Development

Start the development server (frontend and backend):

```bash
npm run dev
```

- Frontend: http://localhost:5173
- Backend: http://localhost:3001 (via `src/backend/server.js`)

---

## 📮 Backend Server (Contact Form)

To start the backend Express server separately:

```bash
node src/backend/server.js
```

- This server handles:
  - reCAPTCHA token validation
  - Email sending via Gmail SMTP
  - Google Sheets submission (optional)

---

## 🏗️ Build for Production

```bash
npm run build
```

- This generates static files inside the `dist/` directory.

To preview the production build:

```bash
npm run preview
```

---

## ☁️ Firebase Deployment

1. Install Firebase CLI (if not already installed):

   ```bash
   npm install -g firebase-tools
   ```

2. Log in to Firebase:

   ```bash
   firebase login
   ```

3. Initialize Firebase (if first time):

   ```bash
   firebase init
   ```

4. Deploy the site:

   ```bash
   npm run deploy
   ```

Or run the deployment batch file:

```bash
./deploy.bat
```

---

## 📁 Project Structure

```
/
├── .firebase/              # Firebase deployment cache
├── public/                 # Static assets
├── src/
│   ├── assets/             # CSS, images, etc.
│   ├── backend/            # Express server for contact form
│   ├── components/         # Vue components
│   ├── locales/            # i18n translation files
│   │   ├── ar/             # Arabic translations
│   │   └── en/             # English translations
│   ├── views/              # Vue views/pages
│   ├── App.vue             # Root component
│   ├── config.js           # App configuration
│   ├── main.js             # App entry point
│   ├── metaTags.js         # SEO meta tags handler
│   ├── navFuntions.js      # Navigation utilities
│   ├── routes.js           # Route definitions
│   ├── utilits.js          # General utilities
│   └── waves.js            # Animation utilities
├── .env                    # Base environment variables
├── .env.development        # Development environment variables
├── .env.production         # Production environment variables
├── firebase.json           # Firebase configuration
└── vite.config.js          # Vite configuration
```

---

## ✨ Features

- Bilingual support (Arabic/English) with full RTL/LTR layout support
- Contact form with Google reCAPTCHA integration
- Custom cursor with smooth animation
- Responsive design for all screen sizes
- Animated section transitions using GSAP
- SEO-optimized meta tags for better visibility
- Video embedding support for workshops section
- Dark mode–friendly theme and layout

---

## 📄 License

This project is **private** and **not licensed** for public or commercial use.

---

## 🙌 Credits

- Designed & Developed by **Hassan Almalki**
- Source code is **copyright protected** and for **personal use only**
