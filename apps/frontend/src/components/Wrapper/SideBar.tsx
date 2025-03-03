import Link from "next/link";
import { usePathname } from "next/navigation";

import { Box, List, ListItem as MUIListItem, ListItemButton, Stack } from "@mui/material";

const URLS = [
  { pathname: "/", label: "Home" },
  { pathname: "/endpoints", label: "Endpoints" },
  { pathname: "/users", label: "Users" },
  { pathname: "/data-source", label: "Data Source" }
];

function ListItem(url: { pathname: string; label: string }) {
  const pathname = usePathname();
  const isActive =
    url.pathname === "/" ? pathname === url.pathname : pathname.includes(url.pathname);
  return (
    <MUIListItem
      key={url.pathname}
      sx={{
        position: "relative",
        paddingY: 0,
        paddingRight: 2,
        paddingLeft: isActive ? 0 : 2
      }}
    >
      <Stack direction="row" spacing={2} width="100%">
        {isActive && (
          <Box
            sx={{
              content: "''",
              display: "inline-block",
              height: "100%",
              width: 5,
              backgroundColor: ({ palette }) => `${palette.primary.main}!important`,
              position: "absolute",
              top: 0,
              left: 0,
              borderRadius: "0 0.6rem 0.6rem 0"
            }}
          ></Box>
        )}
        <ListItemButton
          component={Link}
          href={url.pathname}
          sx={{
            paddingY: 2,
            borderRadius: "0.6rem",
            backgroundColor: ({ palette }) =>
              isActive ? `${palette.primary.main}!important` : "transparent",
            color: ({ palette }) => (isActive ? palette.common.white : "inherit")
          }}
        >
          {url.label}
        </ListItemButton>
      </Stack>
    </MUIListItem>
  );
}

export default function SideBar() {
  return (
    <Stack>
      <List>{URLS.map((url) => ListItem(url))}</List>
    </Stack>
  );
}
