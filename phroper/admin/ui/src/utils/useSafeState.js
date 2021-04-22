import { useCallback, useEffect, useRef, useState } from "react";

/**
 * useSafeState is a wrapper hook for react useState
 * disables state updates on deattached components
 * @param {any} initialValue Initial value of useState
 * @returns state object and setState function
 */

export default function useSafeState(initialValue) {
  const [state, setState] = useState(initialValue);

  const isAttachedRef = useRef(false);

  const setStateSafe = useCallback(
    (value) => {
      if (isAttachedRef.current) setState(value);
    },
    [setState, isAttachedRef]
  );

  useEffect(() => {
    isAttachedRef.current = true;
    return () => void (isAttachedRef.current = false);
  }, [isAttachedRef]);

  return [state, setStateSafe];
}
