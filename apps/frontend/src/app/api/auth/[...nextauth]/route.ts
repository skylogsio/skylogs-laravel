import NextAuth from "next-auth";

import { authOptions } from "@/services/next-auth/authOptions";

const handler = NextAuth(authOptions);

export { handler as GET, handler as POST };
