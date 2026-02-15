/**
 * Ziggy route helper
 * Uses Laravel's named routes via Ziggy
 */

// The route function is injected by @routes directive in blade
export const route = window.route || ((name, params) => {
  console.warn('Ziggy routes not loaded, falling back to name:', name);
  return `/${name.replace(/\./g, '/')}`;
});

export default route;
