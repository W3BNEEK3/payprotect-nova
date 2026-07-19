/**
 * NovaTrust Charts — minimal SVG line/bar renderer
 * Source: novatrust-design-system.md Section 8
 */
(function () {
  'use strict';

  const NS = 'http://www.w3.org/2000/svg';
  const PADDING = { top: 16, right: 16, bottom: 28, left: 16 };

  function el(tag, attrs) {
    const e = document.createElementNS(NS, tag);
    Object.keys(attrs || {}).forEach((k) => e.setAttribute(k, attrs[k]));
    return e;
  }

  function ensureTooltip(container) {
    let tooltip = container.querySelector('.chart-tooltip');
    if (!tooltip) {
      tooltip = document.createElement('div');
      tooltip.className = 'chart-tooltip';
      tooltip.innerHTML = '<span class="chart-tooltip-value"></span><span class="chart-tooltip-label"></span>';
      container.style.position = 'relative';
      container.appendChild(tooltip);
    }
    return tooltip;
  }

  function showTooltip(container, tooltip, x, y, valueText, labelText) {
    tooltip.querySelector('.chart-tooltip-value').textContent = valueText;
    tooltip.querySelector('.chart-tooltip-label').textContent = labelText;
    tooltip.style.left = x + 'px';
    tooltip.style.top = (y - 12) + 'px';
    tooltip.style.transform = 'translate(-50%, -100%)';
    tooltip.classList.add('visible');
  }

  function hideTooltip(tooltip) {
    tooltip.classList.remove('visible');
  }

  function emptyState(container, message, icon) {
    container.innerHTML =
      '<div class="chart-empty-state">' +
      '<span class="material-symbols-outlined">' + (icon || 'show_chart') + '</span>' +
      '<p>' + message + '</p>' +
      '</div>';
  }

  function computeScale(points, height) {
    const values = points.map((p) => p.value);
    const max = Math.max(...values, 0);
    const min = Math.min(...values, 0);
    const range = max - min || 1;

    return {
      max,
      min,
      yFor: (v) => height - PADDING.bottom - ((v - min) / range) * (height - PADDING.top - PADDING.bottom),
    };
  }

  function drawGridlines(svg, width, height, count = 4) {
    for (let i = 0; i <= count; i++) {
      const y = PADDING.top + ((height - PADDING.top - PADDING.bottom) / count) * i;
      svg.appendChild(el('line', {
        class: 'chart-gridline',
        x1: PADDING.left, x2: width - PADDING.right, y1: y, y2: y,
      }));
    }
  }

  function line(container, options) {
    const { points, color = 'var(--color-teal)', formatValue = (v) => String(v) } = options;

    if (!points || points.length === 0) {
      emptyState(container, 'No data yet for this period.');
      return;
    }

    container.innerHTML = '';
    const width = container.clientWidth || 600;
    const height = 220;

    const svg = el('svg', { class: 'chart-svg', viewBox: `0 0 ${width} ${height}` });
    drawGridlines(svg, width, height);

    const scale = computeScale(points, height);
    const usableWidth = width - PADDING.left - PADDING.right;
    const stepX = points.length > 1 ? usableWidth / (points.length - 1) : 0;

    const pathData = points
      .map((p, i) => {
        const x = PADDING.left + i * stepX;
        const y = scale.yFor(p.value);
        return (i === 0 ? 'M' : 'L') + x + ',' + y;
      })
      .join(' ');

    svg.appendChild(el('path', { class: 'chart-line', d: pathData, stroke: color }));

    const tooltip = ensureTooltip(container);

    points.forEach((p, i) => {
      const x = PADDING.left + i * stepX;
      const y = scale.yFor(p.value);

      const point = el('circle', {
        class: 'chart-point', cx: x, cy: y, r: 3.5, fill: color,
      });
      point.addEventListener('mouseenter', () =>
        showTooltip(container, tooltip, x, y, formatValue(p.value), p.label)
      );
      point.addEventListener('mouseleave', () => hideTooltip(tooltip));
      svg.appendChild(point);

      if (points.length <= 12) {
        const label = el('text', {
          class: 'chart-axis-label', x, y: height - 8, 'text-anchor': 'middle',
        });
        label.textContent = p.label;
        svg.appendChild(label);
      }
    });

    container.appendChild(svg);
  }

  function bar(container, options) {
    const { points, color = 'var(--color-teal)', formatValue = (v) => String(v) } = options;

    if (!points || points.length === 0) {
      emptyState(container, 'No data yet for this period.', 'bar_chart');
      return;
    }

    container.innerHTML = '';
    const width = container.clientWidth || 600;
    const height = 220;

    const svg = el('svg', { class: 'chart-svg', viewBox: `0 0 ${width} ${height}` });
    drawGridlines(svg, width, height);

    const scale = computeScale(points, height);
    const usableWidth = width - PADDING.left - PADDING.right;
    const slot = usableWidth / points.length;
    const barWidth = Math.min(slot * 0.55, 48);

    const baselineY = scale.yFor(0);
    const tooltip = ensureTooltip(container);

    points.forEach((p, i) => {
      const slotCenter = PADDING.left + slot * i + slot / 2;
      const x = slotCenter - barWidth / 2;
      const y = scale.yFor(p.value);
      const barHeight = Math.abs(baselineY - y);

      const rect = el('rect', {
        class: 'chart-bar',
        x, y: Math.min(y, baselineY),
        width: barWidth, height: barHeight,
        rx: 3,
        fill: p.color || color,
      });
      rect.addEventListener('mouseenter', () =>
        showTooltip(container, tooltip, slotCenter, Math.min(y, baselineY), formatValue(p.value), p.label)
      );
      rect.addEventListener('mouseleave', () => hideTooltip(tooltip));
      svg.appendChild(rect);

      const label = el('text', {
        class: 'chart-axis-label', x: slotCenter, y: height - 8, 'text-anchor': 'middle',
      });
      label.textContent = p.label;
      svg.appendChild(label);
    });

    container.appendChild(svg);
  }

  window.NovaChart = { line, bar };
})();
