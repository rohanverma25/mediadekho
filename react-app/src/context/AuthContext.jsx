import React, { createContext, useContext, useState } from 'react';
import { AUTH_TOKEN_STORAGE_KEY } from '../services/api';
import { login as loginRequest, logout as logoutRequest, registerAccount } from '../services/authService';

const AuthContext = createContext();

const USER_STORAGE_KEY = 'ep_auth_user';

function readStoredUser() {
  try {
    const saved = localStorage.getItem(USER_STORAGE_KEY);
    return saved ? JSON.parse(saved) : null;
  } catch {
    return null;
  }
}

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(readStoredUser);
  const [token, setToken] = useState(() => localStorage.getItem(AUTH_TOKEN_STORAGE_KEY));

  const persistSession = ({ token: newToken, user: newUser }) => {
    localStorage.setItem(AUTH_TOKEN_STORAGE_KEY, newToken);
    localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(newUser));
    setToken(newToken);
    setUser(newUser);
  };

  // Merges a partial/full user object into the cached session — used after
  // a profile save so the header/sidebar reflect the new name/email
  // immediately, without forcing a re-login.
  const updateUser = (updatedUser) => {
    setUser((prev) => {
      const merged = { ...prev, ...updatedUser };
      localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(merged));
      return merged;
    });
  };

  const clearSession = () => {
    localStorage.removeItem(AUTH_TOKEN_STORAGE_KEY);
    localStorage.removeItem(USER_STORAGE_KEY);
    setToken(null);
    setUser(null);
  };

  const login = async (credentials) => {
    const data = await loginRequest(credentials);
    persistSession(data);
    return data;
  };

  const register = async (payload) => {
    const data = await registerAccount(payload);

    // A B2B signup comes back pending admin approval — no token/user is
    // issued yet, so there's no session to persist until an admin approves
    // the account and they log in for the first time.
    if (data.token) {
      persistSession(data);
    }

    return data;
  };

  const logout = async () => {
    try {
      if (token) await logoutRequest();
    } catch {
      // Token may already be invalid/expired server-side — clear local
      // session regardless so the user isn't stuck "logged in" client-side.
    } finally {
      clearSession();
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        token,
        isAuthenticated: Boolean(token),
        login,
        register,
        logout,
        updateUser,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
