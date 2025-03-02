export default function ZabbixIcon({ size }: { size: string | number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
      <rect width="200" height="200" fill="#D32F2F" />
      <text
        x="50%"
        y="50%"
        font-family="Arial, sans-serif"
        font-size="150"
        font-weight="bold"
        fill="white"
        text-anchor="middle"
        alignment-baseline="central"
      >
        Z
      </text>
    </svg>
  );
}
