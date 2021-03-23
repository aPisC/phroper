import { useMemo, useState } from "react";

const useLocalStorage = (key, initialValue) => {
  const _loadedState = useMemo(() => {
    let s = initialValue;
    try {
      const rs = localStorage.getItem(key);
      if (rs) s = JSON.parse(localStorage.getItem(key));
      else localStorage.setItem(key, JSON.stringify(initialValue));
    } catch {
      localStorage.setItem(key, JSON.stringify(initialValue));
    }
    return s;
    // eslint-disable-next-line
  }, []);

  const [state, _setState] = useState({
    isInitialized: true,
    value: _loadedState,
  });

  const setState = (newState) => {
    if (!Object.is(state.value, newState)) {
      newState =
        typeof newState === "function" ? newState(state.value) : newState;
      localStorage.setItem(key, JSON.stringify(newState));
      _setState({ ...state, value: newState });
    }
  };

  return [state.value, setState];
};

export default useLocalStorage;
