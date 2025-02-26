import { alpha, IconButton, Stack } from "@mui/material";
import { HiKey, HiPencil, HiTrash } from "react-icons/hi";

interface ActionColumnProps {
  onEdit?: () => void;
  onDelete?: () => void;
  onChangePassword?: () => void;
}

export default function ActionColumn({ onEdit, onDelete, onChangePassword }: ActionColumnProps) {
  return (
    <Stack direction="row" spacing={1} justifyContent="center">
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
      {onChangePassword && (
        <IconButton
          onClick={onChangePassword}
          sx={({ palette }) => ({
            color: palette.secondary.main,
            backgroundColor: alpha(palette.secondary.dark, 0.05)
          })}
        >
          <HiKey size="1.3rem" />
        </IconButton>
      )}
      {onDelete && (
        <IconButton
          onClick={onDelete}
          sx={({ palette }) => ({
            color: palette.error.light,
            backgroundColor: alpha(palette.error.light, 0.05)
          })}
        >
          <HiTrash size="1.4rem" />
        </IconButton>
      )}
    </Stack>
  );
}
