/**
 * notify.js — shared reminder notification helper.
 *
 * Include this on any page (Dashboard, Calendar, Reminder list) after
 * setting a `REMINDER_NOTIFY_URL` global that points at
 * "Anjum Safa/reminder_notify.php" (path depends on which folder the
 * including page lives in).
 *
 * What it does:
 *  - Fetches today's reminders + the next few upcoming reminders.
 *  - Shows a real browser notification (Web Notification API) for any
 *    reminder whose date is today, once per reminder per day.
 *  - Lights up the little red dot on the notification bell (#notifBadge)
 *    when there is at least one reminder due today.
 *  - Fills in a dropdown list (#notifDropdown) with the upcoming reminders,
 *    if that element exists on the page.
 */
(function () {
  if (typeof REMINDER_NOTIFY_URL === 'undefined' || !REMINDER_NOTIFY_URL) {
    return;
  }

  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, function (m) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
    });
  }

  function formatTime(t) {
    return t ? t.slice(0, 5) : '';
  }

  function getNotifiedStore() {
    var todayKey = new Date().toISOString().split('T')[0];
    var store;
    try {
      store = JSON.parse(localStorage.getItem('notifiedReminders') || 'null');
    } catch (e) {
      store = null;
    }
    if (!store || store.date !== todayKey) {
      store = { date: todayKey, ids: [] };
    }
    return store;
  }

  function saveNotifiedStore(store) {
    localStorage.setItem('notifiedReminders', JSON.stringify(store));
  }

  function showBrowserNotification(reminder) {
    var bodyLines = [];
    if (reminder.reminder_time) bodyLines.push('Time: ' + formatTime(reminder.reminder_time));
    if (reminder.notes) bodyLines.push(reminder.notes);
    try {
      new Notification('Reminder: ' + reminder.title, { body: bodyLines.join('\n') });
    } catch (e) {
      // Notifications not supported / blocked — fail silently.
    }
  }

  function updateBadge(count) {
    var badge = document.getElementById('notifBadge');
    if (!badge) return;
    badge.style.display = count > 0 ? 'block' : 'none';
  }

  function renderDropdown(list) {
    var dropdown = document.getElementById('notifDropdown');
    if (!dropdown) return;

    if (!list || !list.length) {
      dropdown.innerHTML = '<div class="notif-empty">No upcoming reminders</div>';
      return;
    }

    dropdown.innerHTML = list.map(function (r) {
      return (
        '<div class="notif-item">' +
        '<strong>' + escapeHtml(r.title) + '</strong>' +
        '<span>' + escapeHtml(r.reminder_date) + (r.reminder_time ? ' \u2022 ' + escapeHtml(formatTime(r.reminder_time)) : '') + '</span>' +
        '</div>'
      );
    }).join('');
  }

  function notifyDueToday(todayReminders) {
    if (!('Notification' in window)) return;

    var store = getNotifiedStore();

    function fireAll() {
      var changed = false;
      (todayReminders || []).forEach(function (reminder) {
        if (store.ids.indexOf(reminder.id) === -1) {
          showBrowserNotification(reminder);
          store.ids.push(reminder.id);
          changed = true;
        }
      });
      if (changed) saveNotifiedStore(store);
    }

    if (Notification.permission === 'granted') {
      fireAll();
    } else if (Notification.permission !== 'denied') {
      Notification.requestPermission().then(function (permission) {
        if (permission === 'granted') fireAll();
      });
    }
  }

  function checkReminders() {
    fetch(REMINDER_NOTIFY_URL, { credentials: 'same-origin' })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (!data || data.error) return;

        var today = data.today || [];
        var upcoming = data.upcoming || [];

        updateBadge(today.length);
        renderDropdown(upcoming);
        notifyDueToday(today);
      })
      .catch(function () {
        // Silently ignore network / auth errors so the page keeps working.
      });
  }

  document.addEventListener('DOMContentLoaded', checkReminders);
  // Re-check every 5 minutes in case the tab is left open across midnight.
  setInterval(checkReminders, 5 * 60 * 1000);
})();
