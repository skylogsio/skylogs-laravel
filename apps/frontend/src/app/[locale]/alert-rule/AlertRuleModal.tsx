import { useEffect, useState } from "react";

import {
  backdropClasses,
  Box,
  Button,
  buttonClasses,
  ButtonGroup,
  Fade,
  Modal,
  Paper,
  useTheme
} from "@mui/material";
import { useQuery } from "@tanstack/react-query";

import type { IAlertRule } from "@/@types/alertRule";
import type { CreateUpdateModal } from "@/@types/global";
import { getAlertRuleCreateData } from "@/api/alertRule/alertRule";
import ClientAPIForm from "@/components/AlertRule/Forms/ClientAPIForm";
import NotificationForm from "@/components/AlertRule/Forms/NotificationForm";
import PrometheusForm from "@/components/AlertRule/Forms/PrometheusForm";
import type { ModalContainerProps } from "@/components/Modal/types";
import { ALERT_RULE_VARIANTS, type AlertRuleType } from "@/utils/alertRuleUtils";

type AlertRuleModalProps = Pick<ModalContainerProps, "open" | "onClose"> & {
  data: CreateUpdateModal<unknown>;
  onSubmit: () => void;
};

export default function AlertRuleModal({ open, onClose, onSubmit, data }: AlertRuleModalProps) {
  const { palette } = useTheme();
  const [selectedDataSource, setSelectedDataSource] = useState<AlertRuleType>(
    ALERT_RULE_VARIANTS[0].value
  );

  useQuery({
    queryKey: ["alert-rule-create-data"],
    queryFn: () => getAlertRuleCreateData()
  });

  function handleShowForm() {
    switch (selectedDataSource) {
      case "api":
        return (
          <ClientAPIForm
            onClose={onClose}
            data={data as CreateUpdateModal<IAlertRule>}
            onSubmit={onSubmit}
          />
        );
      case "prometheus":
        return (
          <PrometheusForm
            data={data as CreateUpdateModal<IAlertRule>}
            onSubmit={onSubmit}
            onClose={onClose}
          />
        );
      case "notification":
        return (
          <NotificationForm
            data={data as CreateUpdateModal<IAlertRule>}
            onSubmit={onSubmit}
            onClose={onClose}
          />
        );
    }
  }

  useEffect(() => {
    if (data !== "NEW") {
      setSelectedDataSource((data as { type: AlertRuleType }).type);
    }
  }, [data]);

  return (
    <Modal
      open={open}
      onClose={onClose}
      aria-labelledby="modal-modal-title"
      aria-describedby="modal-modal-description"
      sx={{
        [`& .${backdropClasses.root}`]: {
          backdropFilter: "blur(4px)"
        }
      }}
    >
      <Fade in={open}>
        <Box
          width="100%"
          maxWidth="1000px"
          maxHeight="90vh"
          sx={{
            position: "absolute",
            top: "50%",
            left: "50%",
            transform: "translate(-50%,-50%)"
          }}
        >
          <Paper
            sx={{ borderRadius: "0.7rem", boxShadow: "none", overflow: "hidden", display: "flex" }}
          >
            <ButtonGroup
              variant="text"
              orientation="vertical"
              sx={{ paddingX: "1rem", paddingY: 1, borderRight: `1px solid ${palette.divider}` }}
            >
              {ALERT_RULE_VARIANTS.map((item, index) => (
                <Button
                  startIcon={item.icon}
                  key={index}
                  onClick={() => setSelectedDataSource(item.value)}
                  sx={{
                    paddingX: 3,
                    paddingY: 1.7,
                    justifyContent: "flex-start",
                    textTransform: "capitalize",
                    borderColor: `${palette.divider} !important`,
                    color:
                      item.value === selectedDataSource
                        ? palette.primary.main
                        : palette.secondary.dark,
                    fontSize: "1rem",
                    [`& .${buttonClasses.icon}`]: {
                      width: 24,
                      height: 24,
                      marginRight: 2,
                      "& svg": {
                        width: "inherit",
                        height: "inherit"
                      }
                    },
                    "&::before": {
                      content: "''",
                      display: item.value === selectedDataSource ? "initial" : "none",
                      position: "absolute",
                      top: 0,
                      left: 0,
                      width: "10px",
                      height: "100%",
                      transform: "translateX(-1rem)",
                      backgroundColor: palette.primary.main
                    }
                  }}
                >
                  {item.value}
                </Button>
              ))}
            </ButtonGroup>
            <Box maxHeight="90vh" overflow="auto" flex={1}>
              {handleShowForm()}
            </Box>
          </Paper>
        </Box>
      </Fade>
    </Modal>
  );
}
