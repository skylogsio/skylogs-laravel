import React from "react";

import { Card, CardContent, Typography, Box, Chip, LinearProgress } from "@mui/material";
import { styled, keyframes } from "@mui/material/styles";
import { TbCircleCheck, TbAlertTriangle, TbExclamationCircle } from "react-icons/tb";

import { IStatusCard } from "@/@types/status";

const pulse = keyframes`
  0% { opacity: 1; }
  50% { opacity: 0.6; }
  100% { opacity: 1; }
`;

const glow = keyframes`
  0% { box-shadow: 0 0 5px rgba(255, 193, 7, 0.3); }
  50% { box-shadow: 0 0 20px rgba(255, 193, 7, 0.6); }
  100% { box-shadow: 0 0 5px rgba(255, 193, 7, 0.3); }
`;

const criticalGlow = keyframes`
  0% { box-shadow: 0 0 5px rgba(244, 67, 54, 0.3); }
  50% { box-shadow: 0 0 20px rgba(244, 67, 54, 0.6); }
  100% { box-shadow: 0 0 5px rgba(244, 67, 54, 0.3); }
`;

const shimmer = keyframes`
  0% { background-position: -200px 0; }
  100% { background-position: calc(200px + 100%) 0; }
`;

const StatusCard = styled(Card)<{ status: "resolve" | "warning" | "critical" }>(
  ({ theme, status }) => ({
    width: 340,
    background:
      status === "resolve"
        ? "linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%)"
        : status === "warning"
          ? "linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%)"
          : "linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%)",
    border: `2px solid ${
      status === "resolve" ? "#4caf50" : status === "warning" ? "#ff9800" : "#f44336"
    }`,
    borderRadius: 16,
    position: "relative",
    overflow: "hidden",
    transition: "all 0.3s ease-in-out",
    cursor: "pointer",
    animation:
      status === "warning"
        ? `${glow} 2s infinite ease-in-out`
        : status === "critical"
          ? `${criticalGlow} 1.5s infinite ease-in-out`
          : "none",
    "&:hover": {
      transform: "translateY(-4px) scale(1.02)",
      boxShadow: theme.shadows[8]
    },
    "&::before": {
      content: "''",
      position: "absolute",
      top: 0,
      left: "-200px",
      width: "200px",
      height: "100%",
      background: "linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent)",
      animation: `${shimmer} 3s infinite`
    }
  })
);

const StatusIcon = styled(Box)<{ status: "resolve" | "warning" | "critical" }>(({ status }) => ({
  display: "flex",
  alignItems: "center",
  justifyContent: "center",
  width: 40,
  height: 40,
  borderRadius: "50%",
  backgroundColor: status === "resolve" ? "#4caf50" : status === "warning" ? "#ff9800" : "#f44336",
  color: "white",
  animation: status !== "resolve" ? `${pulse} 2s infinite ease-in-out` : "none"
}));

const CounterChip = styled(Chip)<{ severity: "warning" | "critical" }>(({ severity }) => ({
  height: 24,
  fontSize: "0.75rem",
  fontWeight: 600,
  backgroundColor: severity === "critical" ? "#f44336" : "#ff9800",
  color: "white",
  "& .MuiChip-label": {
    padding: "0 8px"
  }
}));

const HealthBar = styled(LinearProgress)<{ status: "resolve" | "warning" | "critical" }>(
  ({ status }) => ({
    height: 6,
    borderRadius: 3,
    backgroundColor: "rgba(255,255,255,0.3)",
    "& .MuiLinearProgress-bar": {
      backgroundColor:
        status === "resolve" ? "#4caf50" : status === "warning" ? "#ff9800" : "#f44336",
      borderRadius: 3
    }
  })
);

const getStatusIcon = (status: "resolve" | "warning" | "critical") => {
  switch (status) {
    case "resolve":
      return <TbCircleCheck size={20} />;
    case "warning":
      return <TbAlertTriangle size={20} />;
    case "critical":
      return <TbExclamationCircle size={20} />;
    default:
      return <TbCircleCheck size={20} />;
  }
};

const getHealthPercentage = (
  status: "resolve" | "warning" | "critical",
  criticalCount: number,
  warningCount: number
) => {
  if (status === "resolve" && criticalCount === 0 && warningCount === 0) return 100;
  if (status === "resolve") return 85;
  if (status === "warning") return 60;
  return 25;
};

const formatTimeAgo = (dateString: string) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffInHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60));

  if (diffInHours < 1) return "Just now";
  if (diffInHours < 24) return `${diffInHours}h ago`;
  return `${Math.floor(diffInHours / 24)}d ago`;
};

const StatusMonitoringCards = ({ info }: { info: IStatusCard }) => {
  return (
    <StatusCard key={info.id} status={info.status}>
      <CardContent
        sx={{ padding: "16px !important", height: "100%", position: "relative", zIndex: 1 }}
      >
        <Box
          sx={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "flex-start",
            mb: 1
          }}
        >
          <Typography
            variant="h6"
            sx={{
              fontWeight: 700,
              fontSize: "1.1rem",
              color: "#333",
              lineHeight: 1.2,
              maxWidth: "160px"
            }}
          >
            {info.name}
          </Typography>
          <StatusIcon status={info.status}>{getStatusIcon(info.status)}</StatusIcon>
        </Box>
        <Box sx={{ display: "flex", gap: 1, mb: 2, flexWrap: "wrap" }}>
          {info.criticalCount > 0 && (
            <CounterChip
              label={`${info.criticalCount} Critical`}
              severity="critical"
              size="small"
            />
          )}
          {info.warningCount > 0 && (
            <CounterChip label={`${info.warningCount} Warning`} severity="warning" size="small" />
          )}
          {info.criticalCount === 0 && info.warningCount === 0 && (
            <Chip
              label="All Systems OK"
              size="small"
              sx={{
                backgroundColor: "#4caf50",
                color: "white",
                fontWeight: 600
              }}
            />
          )}
        </Box>
        <Box sx={{ mb: 1.5 }}>
          <Typography
            variant="caption"
            sx={{ color: "#666", fontWeight: 500, mb: 0.5, display: "block" }}
          >
            System Health
          </Typography>
          <HealthBar
            variant="determinate"
            value={getHealthPercentage(info.status, info.criticalCount, info.warningCount)}
            status={info.status}
          />
        </Box>
        <Box sx={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
          <Typography variant="caption" sx={{ color: "#666", fontWeight: 500 }}>
            Updated {formatTimeAgo(info.updated_at)}
          </Typography>
          <Box
            sx={{
              width: 8,
              height: 8,
              borderRadius: "50%",
              backgroundColor:
                info.status === "resolve"
                  ? "#4caf50"
                  : info.status === "warning"
                    ? "#ff9800"
                    : "#f44336",
              animation: info.status !== "resolve" ? `${pulse} 1s infinite ease-in-out` : "none"
            }}
          />
        </Box>
      </CardContent>
    </StatusCard>
  );
};

export default StatusMonitoringCards;
