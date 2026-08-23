"use client";

import { useEffect, useState } from "react";
import { User, getUser } from "./api";

/** undefined = still checking, null = guest, User = logged in */
export function useCurrentUser() {
  const [user, setUser] = useState<User | null | undefined>(undefined);

  useEffect(() => {
    let cancelled = false;
    getUser().then((u) => {
      if (!cancelled) setUser(u);
    });
    return () => {
      cancelled = true;
    };
  }, []);

  return user;
}

/** Same as useCurrentUser, but redirects guests to /login. */
export function useRequireAuth() {
  const user = useCurrentUser();

  useEffect(() => {
    if (user === null) {
      window.location.href = "/login";
    }
  }, [user]);

  return user;
}
