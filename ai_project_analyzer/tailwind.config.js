/** @type {import('tailwindcss').Config} */

module.exports = {
  prefix: "ai-",
  safelist: [
    "ai-shadow-card",
    "ai-bg-empty-illustration",
    "ai-animate-success-pulse",
    "ai-animate-wand-hit",
    "ai-section-title",
    "ai-paragraph",
    "ai-list",
    "ai-list-item",
    "ai-action-list",
    "ai-action-item",
    "ai-action-label",
    "ai-action-content",
    "ai-key-point",
    "ai-key-point-label",
    "ai-key-point-content",
    "ai-timeline",
    "ai-timeline-item",
    "ai-timeline-marker",
    "ai-timeline-content",
    "ai-timeline-title",
    "ai-timeline-description",
    "ai-timeline-status",
    "ai-status-not-started",
    "ai-status-in-progress",
    "ai-status-testing",
    "ai-status-awaiting-feedback",
    "ai-status-completed",
    "ai-status-indicator",
    "ai-in-progress",
    "ai-completed",
    "ai-blocked",
  ],
  content: [
    "./views/**/*.php",
    "./assets/js/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: "#eff6ff",
          100: "#dbeafe",
          200: "#bfdbfe",
          300: "#93c5fd",
          400: "#60a5fa",
          500: "#3b82f6",
          600: "#2563eb",
          700: "#1d4ed8",
          800: "#1e40af",
          900: "#1e3a8a",
        },
      },
      fontFamily: {
        sans: ["Inter", "ui-sans-serif", "system-ui"],
      },
      boxShadow: {
        card: "0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)",
      },
      animation: {
        "success-pulse": "pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite",
        "wand-hit": "bounce 1s ease-in-out",
      },
      backgroundImage: {
        "empty-illustration":
          "url('data:image/svg+xml,%3Csvg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"%236b7280\"%3E%3Cpath stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1\" d=\"M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\" /%3E%3C/svg%3E')",
      },
    },
  },
  plugins: [],
};
