function changeTab(tab) {
    repeat_pick = document.getElementById('repeat-pick');
    event_pick = document.getElementById('event-pick');

    repeat_tab = document.getElementById('repeat-tab');
    event_tab = document.getElementById('event-tab');

    if (tab) {
        // repeat tab
        repeat_pick.className = 'active';
        event_pick.className = 'inactive';
        repeat_tab.style.display = 'block';
        event_tab.style.display = 'none';
    } else {
        // event tab
        repeat_pick.className = 'inactive';
        event_pick.className = 'active';
        repeat_tab.style.display = 'none';
        event_tab.style.display = 'block';
    }
}
