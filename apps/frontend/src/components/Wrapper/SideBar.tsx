import Link from "next/link";
import { usePathname } from "next/navigation";

import { Box, List, ListItem as MUIListItem, ListItemButton, Stack } from "@mui/material";

import { useRole } from "@/hooks";
import { RoleType } from "@/utils/userUtils";

type URLType = { pathname: string; label: string; role?: RoleType | RoleType[] };

const URLS: Array<URLType> = [
  { pathname: "/", label: "Home" },
  { pathname: "/endpoints", label: "Endpoints" },
  { pathname: "/users", label: "Users", role: ["owner", "manager"] },
  { pathname: "/data-source", label: "Data Sources", role: ["owner", "manager"] },
  { pathname: "/clusters", label: "Clusters", role: ["owner"] },
  { pathname: "/alert-rule", label: "Alert Rules" },
  { pathname: "/profile-services", label: "Profile Services", role: "owner" },
  { pathname: "/settings/telegram", label: "Settings", role: "owner" }
];

function ListItem(url: URLType) {
  const pathname = usePathname();
  const { hasRole } = useRole();

  if (url.role) {
    if (!hasRole(url.role)) return;
  }

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
