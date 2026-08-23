"use client";

import { useEffect, useRef } from "react";
import { Chart, ChartConfiguration, registerables } from "chart.js";

Chart.register(...registerables);

export default function TrendChart({
  config,
  height = 300,
}: {
  config: ChartConfiguration | Record<string, unknown>;
  height?: number;
}) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const chartRef = useRef<Chart | null>(null);
  const configJson = JSON.stringify(config);

  useEffect(() => {
    if (!canvasRef.current) return;

    chartRef.current = new Chart(canvasRef.current, JSON.parse(configJson) as ChartConfiguration);

    return () => {
      chartRef.current?.destroy();
      chartRef.current = null;
    };
  }, [configJson]);

  return <canvas ref={canvasRef} height={height} />;
}
