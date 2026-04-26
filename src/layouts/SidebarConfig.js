// Sidebar configuration - hide Positions menu item
const sidebarItems = [
  { label: 'Dashboard', icon: 'home', path: '/', visible: true },
  { label: 'Courses', icon: 'book', path: '/courses', visible: true },
  { label: 'Students', icon: 'users', path: '/students', visible: true },
  { label: 'Positions', icon: 'briefcase', path: '/positions', visible: false },  // Hidden per #466
  { label: 'Settings', icon: 'cog', path: '/settings', visible: true },
];

export function getVisibleItems() {
  return sidebarItems.filter(item => item.visible !== false);
}

export default sidebarItems;