/* =========================================================================
   ClubHub v3 - App logic
   ========================================================================= */

(function () {
  'use strict';

  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

  const icon = (name, size = 18) => {
    const svg = CLUBHUB.ICONS[name] || '';
    return svg.replace('<svg ', `<svg width="${size}" height="${size}" `);
  };

  const hideLoader = () => {
    const loader = $('.page-loader');
    if (loader) setTimeout(() => loader.classList.add('hidden'), 200);
  };

  const toast = (title, message = '', type = 'info', duration = 3500) => {
    let container = $('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    const t = document.createElement('div');
    t.className = 'toast';
    const icons = { success: '✓', error: '!', warning: '!', info: 'i' };
    t.innerHTML = `
      <div class="toast-icon">${icons[type] || 'i'}</div>
      <div>
        <div class="toast-title">${title}</div>
        ${message ? `<div class="toast-message">${message}</div>` : ''}
      </div>
    `;
    container.appendChild(t);
    setTimeout(() => {
      t.style.opacity = '0';
      t.style.transform = 'translateX(20px)';
      setTimeout(() => t.remove(), 300);
    }, duration);
  };

  const confirm = (title, message, onConfirm) => {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay open';
    overlay.innerHTML = `
      <div class="modal">
        <div class="modal-header">
          <h3 class="modal-title">${title}</h3>
          <button class="btn-icon" data-close>${icon('x', 16)}</button>
        </div>
        <p style="color: var(--text-secondary); margin-bottom: var(--s-6);">${message}</p>
        <div style="display: flex; gap: var(--s-3); justify-content: flex-end;">
          <button class="btn btn-secondary" data-close>Annuler</button>
          <button class="btn btn-orange" data-confirm>Confirmer</button>
        </div>
      </div>
    `;
    document.body.appendChild(overlay);
    const close = () => overlay.remove();
    overlay.addEventListener('click', e => {
      if (e.target === overlay || e.target.hasAttribute('data-close')) close();
    });
    overlay.querySelector('[data-confirm]').addEventListener('click', () => {
      onConfirm();
      close();
    });
  };

  const formatDate = (iso, opts = {}) => {
    const d = new Date(iso);
    const day = d.getDate();
    const month = d.toLocaleDateString('fr-FR', { month: 'short' });
    if (opts.short) return { day, month };
    return d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  };

  const Auth = {
    current() { return STORAGE.get('current_user'); },
    isAuthenticated() { return !!this.current(); },
    login(role) {
      const account = DEMO_ACCOUNTS.find(a => a.role === role);
      if (!account) return false;
      const user = { ...account, loggedInAt: new Date().toISOString() };
      STORAGE.set('current_user', user);
      return user;
    },
    logout() { STORAGE.remove('current_user'); window.location.href = 'index.html'; },
    require(role = null) {
      const u = this.current();
      if (!u) { window.location.href = 'login.html'; return null; }
      if (role && u.role !== role) { toast('Accès refusé', '', 'error'); return null; }
      return u;
    }
  };

  // Navbar style Webflow (logo + 4 links + 2 icons)
  const renderNavbar = (active = '') => {
    const u = Auth.current();
    return `
      <nav class="navbar">
        <div class="container" style="padding: 0;">
          <div class="navbar-inner">
            <a href="index.html" class="brand">
              <div class="brand-mark">
                <svg viewBox="0 0 32 32" width="22" height="22" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect width="32" height="32" rx="6" fill="#FF4502"/>
                  <circle cx="16" cy="16" r="4" fill="#FFFFFF"/>
                  <ellipse cx="16" cy="16" rx="13" ry="5" stroke="#FFFFFF" stroke-width="2" transform="rotate(-30 16 16)"/>
                  <circle cx="6" cy="9" r="1.5" fill="#C2FF01"/>
                </svg>
              </div>
              <span class="brand-name">Orbit<span>.</span></span>
            </a>
            <div class="nav-links">
              <a href="index.html" class="nav-link ${active === 'home' ? 'active' : ''}">Accueil</a>
              <a href="clubs.html" class="nav-link ${active === 'clubs' ? 'active' : ''}">Annuaire</a>
              <a href="events.html" class="nav-link ${active === 'events' ? 'active' : ''}">Événements</a>
              <a href="agenda.html" class="nav-link ${active === 'agenda' ? 'active' : ''}">Agenda</a>
              <a href="leaderboard.html" class="nav-link ${active === 'leaderboard' ? 'active' : ''}">Classement</a>
              <a href="map.html" class="nav-link ${active === 'map' ? 'active' : ''}">Carte</a>
            </div>
            <div class="nav-actions">
              <button class="nav-icon-btn" id="theme-toggle" title="Basculer theme clair/sombre" onclick="ClubHub.Theme.toggle()">
                <svg id="theme-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5">
                  <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                </svg>
              </button>
              <button class="nav-icon-btn" title="Notifications" onclick="window.location.href='notifications.html'">${icon('bell', 18)}<span class="dot" id="notif-dot"></span></button>
              ${u ? `
                <a href="${u.role === 'admin' ? 'admin.html' : u.role === 'manager' ? 'manager.html' : 'dashboard.html'}" class="nav-avatar" title="${u.name}">
                  <div class="avatar avatar-orange">${u.avatar}</div>
                </a>
              ` : `
                <a href="login.html" class="nav-avatar" style="background: var(--black);">
                  <span style="color: var(--white); font-size: 12px; font-weight: 700;">Go</span>
                </a>
              `}
            </div>
          </div>
        </div>
      </nav>
    `;
  };

  const renderSidebar = (active = '', role = 'student') => {
    const links = {
      student: [
        { section: 'PRINCIPAL' },
        { id: 'dashboard', label: 'Tableau de bord', icon: 'home', href: 'dashboard.html' },
        { id: 'clubs', label: 'Mes clubs', icon: 'layers', href: 'dashboard.html?tab=clubs' },
        { id: 'events', label: 'Mes événements', icon: 'calendar', href: 'dashboard.html?tab=events' },
        { id: 'agenda', label: 'Mon agenda', icon: 'clock', href: 'agenda.html' },
        { id: 'notifications', label: 'Notifications', icon: 'bell', href: 'notifications.html', count: 3 },
        { section: 'COMPTE' },
        { id: 'profile', label: 'Mon profil', icon: 'user', href: 'profile.html' },
        { id: 'settings', label: 'Paramètres', icon: 'settings', href: 'settings.html' },
      ],
      manager: [
        { section: 'GESTION' },
        { id: 'overview', label: 'Vue d\'ensemble', icon: 'home', href: 'manager.html' },
        { id: 'members', label: 'Membres', icon: 'users', href: 'manager.html?tab=members', count: 30 },
        { id: 'events', label: 'Événements', icon: 'calendar', href: 'manager.html?tab=events' },
        { id: 'attendance', label: 'Présences', icon: 'check', href: 'manager.html?tab=attendance' },
        { id: 'comms', label: 'Communications', icon: 'mail', href: 'manager.html?tab=comms' },
        { section: 'DONNÉES' },
        { id: 'stats', label: 'Statistiques', icon: 'chart', href: 'manager.html?tab=stats' },
        { id: 'export', label: 'Exports', icon: 'download', href: 'manager.html?tab=export' },
        { section: 'COMPTE' },
        { id: 'settings', label: 'Paramètres', icon: 'settings', href: 'settings.html' },
      ],
      admin: [
        { section: 'PILOTAGE' },
        { id: 'overview', label: 'Vue d\'ensemble', icon: 'home', href: 'admin.html' },
        { id: 'clubs', label: 'Tous les clubs', icon: 'layers', href: 'admin.html?tab=clubs', count: 12 },
        { id: 'users', label: 'Utilisateurs', icon: 'users', href: 'admin.html?tab=users' },
        { id: 'events', label: 'Événements', icon: 'calendar', href: 'admin.html?tab=events' },
        { section: 'GOUVERNANCE' },
        { id: 'stats', label: 'Statistiques', icon: 'chart', href: 'admin.html?tab=stats' },
        { id: 'integrations', label: 'Intégrations', icon: 'zap', href: 'admin.html?tab=integrations' },
        { id: 'settings', label: 'Paramètres', icon: 'settings', href: 'settings.html' },
      ],
    };

    const roleLinks = links[role] || links.student;
    const groups = [];
    let current = null;
    roleLinks.forEach(l => {
      if (l.section) { current = { label: l.section, items: [] }; groups.push(current); }
      else if (current) current.items.push(l);
    });

    return `
      <aside class="sidebar">
        ${groups.map(g => `
          <div class="sidebar-section">
            <div class="sidebar-label">${g.label}</div>
            ${g.items.map(i => `
              <a href="${i.href}" class="sidebar-link ${active === i.id ? 'active' : ''}">
                ${icon(i.icon, 18)}
                <span>${i.label}</span>
                ${i.count ? `<span class="count">${i.count}</span>` : ''}
              </a>
            `).join('')}
          </div>
        `).join('')}
        <div class="sidebar-section">
          <button class="sidebar-link" onclick="ClubHub.Auth.logout()" style="width:100%;">
            ${icon('logout', 18)}<span>Déconnexion</span>
          </button>
        </div>
      </aside>
    `;
  };

  const renderShell = (options) => {
    const { active, role, content, pageTitle, pageSubtitle } = options;
    const main = $('.app-shell-mount');
    if (!main) return;
    main.innerHTML = `
      ${renderNavbar(active)}
      <div class="app-shell">
        ${renderSidebar(active, role)}
        <main class="main-content">
          ${pageTitle ? `
            <div class="page-header">
              <h1 class="page-title">${pageTitle}</h1>
              ${pageSubtitle ? `<p class="page-subtitle">${pageSubtitle}</p>` : ''}
            </div>
          ` : ''}
          ${content}
        </main>
      </div>
    `;
  };

  const setupReveal = () => {
    const items = $$('.reveal');
    if (!items.length) return;
    const obs = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }
      });
    }, { threshold: 0.1 });
    items.forEach(i => obs.observe(i));
  };

  // Theme toggle (dark / light / high-contrast)
  const Theme = {
    get() { return localStorage.getItem('orbit_theme') || 'light'; },
    set(theme) {
      localStorage.setItem('orbit_theme', theme);
      document.documentElement.setAttribute('data-theme', theme);
      this._updateIcon();
    },
    toggle() {
      const cur = this.get();
      this.set(cur === 'dark' ? 'light' : 'dark');
    },
    _updateIcon() {
      const ico = document.getElementById('theme-icon');
      if (!ico) return;
      ico.innerHTML = this.get() === 'dark'
        ? '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>'
        : '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>';
    },
    init() {
      document.documentElement.setAttribute('data-theme', this.get());
      setTimeout(() => this._updateIcon(), 100);
    }
  };

  // Notifications badge (compte non-lus en live)
  const NotifBadge = {
    init() {
      const u = Auth.current();
      if (!u) return;
      const update = () => {
        const list = window.CLUBHUB.NOTIFICATIONS || [];
        const unread = list.filter(n => !n.read).length;
        const dot = document.getElementById('notif-dot');
        if (dot) dot.style.display = unread > 0 ? 'block' : 'none';
      };
      update();
      // poll toutes les 5s pour le "real-time"
      setInterval(update, 5000);
    }
  };

  window.ClubHub = Object.assign(window.ClubHub || {}, {
    $, $$, icon, hideLoader, toast, confirm, formatDate, Auth,
    renderNavbar, renderSidebar, renderShell, setupReveal,
    Theme, NotifBadge,
  });

  // init theme au plus tot
  Theme.init();
  document.addEventListener('DOMContentLoaded', () => NotifBadge.init());
})();
