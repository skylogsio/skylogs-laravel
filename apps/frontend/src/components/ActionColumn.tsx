import { alpha, Box, IconButton } from "@mui/material";
import { HiPencil, HiTrash } from "react-icons/hi";

interface ActionColumnProps {
  onEdit?: () => void;
  onDelete?: () => void;
}

export default function ActionColumn({ onEdit, onDelete }: ActionColumnProps) {
  return (
    <Box>
      {onEdit && (
        <IconButton
          onClick={onEdit}
          sx={({ palette }) => ({
            color: palette.info.light,
            backgroundColor: alpha(palette.info.light, 0.05)
          })}
        >
          <HiPencil size="1.4rem" />
        </IconButton>
      )}
      {onDelete && (
        <IconButton
          sx={({ palette }) => ({
            color: palette.error.light,
            backgroundColor: alpha(palette.error.light, 0.05),
            marginLeft: "0.5rem"
          })}
        >
          <HiTrash size="1.4rem" />
        </IconButton>
      )}
    </Box>
  );
}
