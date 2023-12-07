/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from "@wordpress/i18n";

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import { Button, PanelBody, Placeholder, TextControl } from "@wordpress/components";
import { blockDefault, keyboardReturn, warning, info, check } from "@wordpress/icons";
import { SVG, Path } from "@wordpress/primitives";
import { cloneElement, useEffect, useRef, useState } from "react";

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import { Popover } from "@wordpress/components";
import "./editor.scss";

/**
 * Custom hook that debounces the given value.
 *
 * @param {any} value - The value to debounce.
 * @param {number} delay - The delay in milliseconds before updating the debounced value.
 * @returns {any} The debounced value.
 */
const useDebounce = (value, delay = 300) => {
  const [debouncedValue, setDebouncedValue] = useState(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
};

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes, ...rest }) {
  const projectId = useDebounce(attributes.projectId, 750);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [block, setBlock] = useState(null);

  const updateProjectId = (projectId) => {
    setAttributes({ projectId });
    setError(null);
    setLoading(false);
    setBlock(null);
  };

  useEffect(() => {
    setLoading(true);
    setBlock(null);
  }, [attributes.projectId]);

  useEffect(() => {
    if (projectId) {
      fetch(`https://bdn.notice.studio/blocks/${encodeURIComponent(projectId)}`)
        .then(async (res) => {
          const result = await res.json();
          if (res.ok && result.success) {
            setError(null);
            setBlock(result.data);
          } else {
            setError(__("Could not find", "noticefaq") + ' "' + projectId + '"');
            setBlock(null);
          }
        })
        .finally(() => {
          setLoading(false);
        });
    } else {
      setLoading(false);
      setBlock(null);
    }
  }, [projectId]);

  return (
    <div {...useBlockProps()}>
      <InspectorControls>
        <PanelBody title={__("Block Properties", "noticefaq")}>
          <TextControl
            label={__("Project ID", "noticefaq")}
            value={attributes.projectId}
            onChange={(val) => updateProjectId(val)}
          />
        </PanelBody>
      </InspectorControls>
      <BlockPlaceholder
        projectId={projectId}
        loading={loading}
        error={error}
        onChangeProjectId={(val) => updateProjectId(val)}
      />
      {projectId && !loading && !error && block != null && (
        <Placeholder
          icon={
            <SVG xmlns="http://www.w3.org/2000/svg" style={{ height: "20px" }} viewBox="0 0 384 512">
              <Path d="M272 384c9.6-31.9 29.5-59.1 49.2-86.2l0 0c5.2-7.1 10.4-14.2 15.4-21.4c19.8-28.5 31.4-63 31.4-100.3C368 78.8 289.2 0 192 0S16 78.8 16 176c0 37.3 11.6 71.9 31.4 100.3c5 7.2 10.2 14.3 15.4 21.4l0 0c19.8 27.1 39.7 54.4 49.2 86.2H272zM192 512c44.2 0 80-35.8 80-80V416H112v16c0 44.2 35.8 80 80 80zM112 176c0 8.8-7.2 16-16 16s-16-7.2-16-16c0-61.9 50.1-112 112-112c8.8 0 16 7.2 16 16s-7.2 16-16 16c-44.2 0-80 35.8-80 80z" />
            </SVG>
          }
          label={block.data.text}
          style={{ cursor: "default" }}
        >
          <div class="components-placeholder__instructions" style={{ display: "flex", alignItems: "center" }}>
            <svg
              width="16"
              height="16"
              xmlns="http://www.w3.org/2000/svg"
              fillRule="evenodd"
              clipRule="evenodd"
              fill="green"
              viewBox="0 0 24 24"
              style={{ marginRight: "6px" }}
            >
              <path d="M12 0c6.623 0 12 5.377 12 12s-5.377 12-12 12-12-5.377-12-12 5.377-12 12-12zm0 1c6.071 0 11 4.929 11 11s-4.929 11-11 11-11-4.929-11-11 4.929-11 11-11zm7 7.457l-9.005 9.565-4.995-5.865.761-.649 4.271 5.016 8.24-8.752.728.685z" />
            </svg>
            {__("Your Notice project is loaded and will be visible on the public and preview page.", "noticefaq")}
          </div>
          <p
            style={{ margin: 0, color: "rgba(0, 0, 0, 0.4)", fontSize: "12px", display: "flex", alignItems: "center" }}
          >
            {cloneElement(info, {
              style: { width: "16px", height: "16px", marginRight: "6px", fill: "cornflowerblue" },
            })}
            Project ID = "{projectId}"
          </p>
        </Placeholder>
      )}
    </div>
  );
}

const BlockPlaceholder = ({ projectId, loading, error, onChangeProjectId }) => {
  const buttonRef = useRef(null);
  const [menuVisible, setMenuVisible] = useState(false);
  const [inputValue, setInputValue] = useState("");

  if (loading) {
    return <Placeholder icon={blockDefault} label={__("Loading...", "noticefaq")}></Placeholder>;
  }

  const onSubmit = () => {
    setMenuVisible(false);

    if (!inputValue) return;

    setInputValue("");
    onChangeProjectId(inputValue);
  };

  const Menu = menuVisible ? (
    <Popover anchor={buttonRef.current} onClose={onSubmit}>
      <form style={{ display: "flex" }} onSubmit={onSubmit}>
        <input
          type="text"
          value={inputValue}
          onChange={(e) => setInputValue(e.target.value)}
          placeholder="Paste your Project ID"
          autoFocus
        />
        <Button icon={keyboardReturn} label={__("Apply")} type="submit"></Button>
      </form>
    </Popover>
  ) : (
    <></>
  );

  if (error) {
    return (
      <Placeholder icon={warning} label={error}>
        <Button ref={buttonRef} onClick={() => setMenuVisible(true)} isPrimary>
          Edit Project ID
        </Button>
        {Menu}
      </Placeholder>
    );
  }

  if (!projectId) {
    return (
      <Placeholder
        icon={blockDefault}
        label={__("Notice block", "noticefaq")}
        instructions={__("You need to edit the project ID of this block to see it appear on your site.", "noticefaq")}
      >
        <Button ref={buttonRef} onClick={() => setMenuVisible(true)} isPrimary>
          Edit Project ID
        </Button>
        {Menu}
      </Placeholder>
    );
  }

  return <></>;
};
