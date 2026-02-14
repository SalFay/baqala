import { atom, selector } from 'recoil';

export const authAtom = atom({
    key: 'authState',
    default: {
        user: null,
        isAuthenticated: false,
        token: null,
    },
});

export const userSelector = selector({
    key: 'userSelector',
    get: ({ get }) => get(authAtom).user,
});

export const isAuthenticatedSelector = selector({
    key: 'isAuthenticatedSelector',
    get: ({ get }) => get(authAtom).isAuthenticated,
});
