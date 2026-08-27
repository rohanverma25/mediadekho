import React, { useEffect, useRef, useState } from 'react';

const DURATION_MS = 1500;

const splitValue = (value) => {
  const match = value.match(/[\d,]+/);
  if (!match) return null;
  return {
    prefix: value.slice(0, match.index),
    target: parseInt(match[0].replace(/,/g, ''), 10),
    suffix: value.slice(match.index + match[0].length),
  };
};

/**
 * Counts up from 0 to the stat's numeric value once the tile actually
 * scrolls into view (IntersectionObserver, fires once) — a static number
 * is instant and easy to skim past; watching it climb is what makes a
 * "700+" register as a real, earned figure instead of homepage filler.
 */
export const StatCounter = ({ value, className }) => {
  const parsed = splitValue(value);
  const [display, setDisplay] = useState(() => (parsed ? `${parsed.prefix}0${parsed.suffix}` : value));
  const ref = useRef(null);

  useEffect(() => {
    if (!parsed || !ref.current) return;

    let frame;
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (!entry.isIntersecting) return;
        observer.disconnect();

        const start = performance.now();
        const tick = (now) => {
          const progress = Math.min((now - start) / DURATION_MS, 1);
          const eased = 1 - (1 - progress) ** 3;
          const current = Math.round(parsed.target * eased);
          setDisplay(`${parsed.prefix}${current.toLocaleString('en-IN')}${parsed.suffix}`);
          if (progress < 1) frame = requestAnimationFrame(tick);
        };
        frame = requestAnimationFrame(tick);
      },
      { threshold: 0.3 },
    );

    observer.observe(ref.current);
    return () => {
      observer.disconnect();
      cancelAnimationFrame(frame);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [value]);

  return (
    <span ref={ref} className={className}>
      {display}
    </span>
  );
};
