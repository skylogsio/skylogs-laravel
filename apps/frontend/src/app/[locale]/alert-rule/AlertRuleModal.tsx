import { useState } from "react";

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

import type { CreateUpdateModal } from "@/@types/global";
import ClientAPIForm from "@/components/AlertRuleForms/ClientAPIForm";
import type { ModalContainerProps } from "@/components/Modal/types";
import { DATA_SOURCE_TYPE } from "@/utils/dataSourceUtils";

type DataSourceModalProps = Pick<ModalContainerProps, "open" | "onClose"> & {
  data: CreateUpdateModal<unknown>;
  onSubmit: () => void;
};

export default function AlertRuleModal({ open, onClose }: DataSourceModalProps) {
  const { palette } = useTheme();
  const [selectedDataSource, setSelectedDataSource] = useState(DATA_SOURCE_TYPE[0].value);

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
              {DATA_SOURCE_TYPE.map((item, index) => (
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
            {selectedDataSource === "clientAPI" && <ClientAPIForm />}
          </Paper>
        </Box>
      </Fade>
    </Modal>
  );
}
