/**
 * Framer Motion Animation Presets
 *
 * Centralized animation variants for consistent motion across the application.
 */

import confetti from 'canvas-confetti';

/**
 * Product card animation variants
 * - Hover: lift up with enhanced shadow
 * - Tap: scale down slightly
 */
export const productCard = {
  initial: {
    opacity: 0,
    y: 20,
    scale: 0.95,
  },
  animate: {
    opacity: 1,
    y: 0,
    scale: 1,
    transition: {
      duration: 0.3,
      ease: [0.4, 0, 0.2, 1],
    },
  },
  exit: {
    opacity: 0,
    scale: 0.9,
    transition: {
      duration: 0.2,
    },
  },
  hover: {
    y: -4,
    scale: 1.02,
    boxShadow: '0 8px 24px rgba(0, 0, 0, 0.12)',
    transition: {
      duration: 0.2,
      ease: [0.4, 0, 0.2, 1],
    },
  },
  tap: {
    scale: 0.98,
    transition: {
      duration: 0.1,
    },
  },
};

/**
 * Cart item animation variants
 * - Slide in from right with fade
 * - Slide out to left with fade
 */
export const cartItem = {
  initial: {
    opacity: 0,
    x: 50,
    height: 0,
  },
  animate: {
    opacity: 1,
    x: 0,
    height: 'auto',
    transition: {
      duration: 0.3,
      ease: [0.4, 0, 0.2, 1],
      height: {
        duration: 0.25,
      },
    },
  },
  exit: {
    opacity: 0,
    x: -50,
    height: 0,
    transition: {
      duration: 0.25,
      ease: [0.4, 0, 1, 1],
      height: {
        delay: 0.1,
      },
    },
  },
};

/**
 * Number change animation variants
 * - Bounce effect for totals and quantities
 */
export const numberChange = {
  initial: {
    scale: 1,
  },
  animate: {
    scale: [1, 1.15, 0.95, 1],
    transition: {
      duration: 0.4,
      ease: [0.68, -0.55, 0.265, 1.55], // bounce easing
    },
  },
};

/**
 * Success pulse animation variants
 * - Celebration checkmark with scale and glow
 */
export const successPulse = {
  initial: {
    scale: 0,
    opacity: 0,
  },
  animate: {
    scale: [0, 1.2, 1],
    opacity: 1,
    transition: {
      duration: 0.5,
      ease: [0.34, 1.56, 0.64, 1], // spring easing
    },
  },
};

/**
 * Checkmark draw animation
 * - SVG path drawing effect
 */
export const checkmarkDraw = {
  initial: {
    pathLength: 0,
    opacity: 0,
  },
  animate: {
    pathLength: 1,
    opacity: 1,
    transition: {
      pathLength: {
        duration: 0.5,
        ease: 'easeOut',
        delay: 0.2,
      },
      opacity: {
        duration: 0.1,
      },
    },
  },
};

/**
 * Fade in animation variants
 */
export const fadeIn = {
  initial: {
    opacity: 0,
  },
  animate: {
    opacity: 1,
    transition: {
      duration: 0.3,
    },
  },
  exit: {
    opacity: 0,
    transition: {
      duration: 0.2,
    },
  },
};

/**
 * Slide up animation variants
 */
export const slideUp = {
  initial: {
    opacity: 0,
    y: 20,
  },
  animate: {
    opacity: 1,
    y: 0,
    transition: {
      duration: 0.3,
      ease: [0.4, 0, 0.2, 1],
    },
  },
  exit: {
    opacity: 0,
    y: -20,
    transition: {
      duration: 0.2,
    },
  },
};

/**
 * Slide in from right animation variants
 */
export const slideInRight = {
  initial: {
    opacity: 0,
    x: '100%',
  },
  animate: {
    opacity: 1,
    x: 0,
    transition: {
      duration: 0.3,
      ease: [0.4, 0, 0.2, 1],
    },
  },
  exit: {
    opacity: 0,
    x: '100%',
    transition: {
      duration: 0.25,
    },
  },
};

/**
 * Scale up animation variants
 */
export const scaleUp = {
  initial: {
    opacity: 0,
    scale: 0.9,
  },
  animate: {
    opacity: 1,
    scale: 1,
    transition: {
      duration: 0.2,
      ease: [0.4, 0, 0.2, 1],
    },
  },
  exit: {
    opacity: 0,
    scale: 0.9,
    transition: {
      duration: 0.15,
    },
  },
};

/**
 * Stagger children animation container
 * @param {number} staggerDelay - Delay between each child
 */
export const staggerContainer = (staggerDelay = 0.05) => ({
  initial: {},
  animate: {
    transition: {
      staggerChildren: staggerDelay,
      delayChildren: 0.1,
    },
  },
  exit: {
    transition: {
      staggerChildren: staggerDelay / 2,
      staggerDirection: -1,
    },
  },
});

/**
 * Stagger child animation variants
 */
export const staggerChild = {
  initial: {
    opacity: 0,
    y: 10,
  },
  animate: {
    opacity: 1,
    y: 0,
    transition: {
      duration: 0.2,
    },
  },
  exit: {
    opacity: 0,
    y: -10,
    transition: {
      duration: 0.15,
    },
  },
};

/**
 * Skeleton loading animation
 */
export const skeleton = {
  initial: {
    backgroundPosition: '-200% 0',
  },
  animate: {
    backgroundPosition: '200% 0',
    transition: {
      duration: 1.5,
      ease: 'linear',
      repeat: Infinity,
    },
  },
};

/**
 * Button press animation
 */
export const buttonPress = {
  tap: {
    scale: 0.95,
    transition: {
      duration: 0.1,
    },
  },
};

/**
 * Icon rotate animation (for theme toggle)
 */
export const iconRotate = {
  initial: { rotate: 0, scale: 0 },
  animate: {
    rotate: 360,
    scale: 1,
    transition: {
      duration: 0.5,
      ease: [0.34, 1.56, 0.64, 1],
    },
  },
  exit: {
    rotate: 0,
    scale: 0,
    transition: {
      duration: 0.3,
    },
  },
};

/**
 * Confetti burst effect wrapper
 * Triggers a celebration confetti animation
 * @param {Object} options - Confetti options
 */
export const confettiBurst = (options = {}) => {
  const defaults = {
    particleCount: 100,
    spread: 70,
    origin: { y: 0.6 },
    colors: ['#1890ff', '#52c41a', '#faad14', '#eb2f96', '#722ed1'],
    zIndex: 9999,
  };

  // Main burst
  confetti({
    ...defaults,
    ...options,
  });

  // Side bursts for extra effect
  setTimeout(() => {
    confetti({
      ...defaults,
      particleCount: 50,
      angle: 60,
      spread: 55,
      origin: { x: 0, y: 0.7 },
      ...options,
    });
  }, 100);

  setTimeout(() => {
    confetti({
      ...defaults,
      particleCount: 50,
      angle: 120,
      spread: 55,
      origin: { x: 1, y: 0.7 },
      ...options,
    });
  }, 200);
};

/**
 * Realistic confetti effect (like fireworks)
 */
export const confettiFireworks = () => {
  const duration = 3000;
  const animationEnd = Date.now() + duration;
  const defaults = {
    startVelocity: 30,
    spread: 360,
    ticks: 60,
    zIndex: 9999,
    colors: ['#1890ff', '#52c41a', '#faad14', '#eb2f96', '#722ed1'],
  };

  const interval = setInterval(() => {
    const timeLeft = animationEnd - Date.now();

    if (timeLeft <= 0) {
      return clearInterval(interval);
    }

    const particleCount = 50 * (timeLeft / duration);

    confetti({
      ...defaults,
      particleCount,
      origin: { x: Math.random() * 0.4 + 0.3, y: Math.random() * 0.3 + 0.3 },
    });
  }, 250);
};

/**
 * Simple confetti rain effect
 */
export const confettiRain = () => {
  confetti({
    particleCount: 150,
    spread: 180,
    origin: { y: 0 },
    startVelocity: 25,
    gravity: 0.5,
    ticks: 300,
    colors: ['#1890ff', '#52c41a', '#faad14', '#eb2f96', '#722ed1'],
    zIndex: 9999,
  });
};

/**
 * Spring transition preset
 */
export const springTransition = {
  type: 'spring',
  stiffness: 300,
  damping: 20,
};

/**
 * Smooth transition preset
 */
export const smoothTransition = {
  duration: 0.3,
  ease: [0.4, 0, 0.2, 1],
};

/**
 * Bounce transition preset
 */
export const bounceTransition = {
  type: 'spring',
  stiffness: 400,
  damping: 10,
};

export default {
  productCard,
  cartItem,
  numberChange,
  successPulse,
  checkmarkDraw,
  fadeIn,
  slideUp,
  slideInRight,
  scaleUp,
  staggerContainer,
  staggerChild,
  skeleton,
  buttonPress,
  iconRotate,
  confettiBurst,
  confettiFireworks,
  confettiRain,
  springTransition,
  smoothTransition,
  bounceTransition,
};
