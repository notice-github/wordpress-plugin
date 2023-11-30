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
import { blockDefault, keyboardReturn, warning } from "@wordpress/icons";
import { useEffect, useRef, useState } from "react";

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

const mandatoryFunctions = (document) => {
  return {
    toggleExpandable(id) {
      const expandable = document.getElementById(id);
      if (!expandable) return null;

      const isExpanded = expandable.getAttribute("aria-expanded");

      if (isExpanded === "true") {
        expandable.setAttribute("aria-expanded", "false");
      } else {
        expandable.setAttribute("aria-expanded", "true");
      }
    },
  };
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
  const containerRef = useRef(null);
  const projectId = useDebounce(attributes.projectId, 750);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const editorWindow = () => containerRef.current.ownerDocument.defaultView;
  const editorDocument = () => containerRef.current.ownerDocument;

  const updateProjectId = (projectId) => {
    setAttributes({ projectId });
    setError(null);
    setLoading(false);
  };

  useEffect(() => {
    setLoading(true);
    containerRef.current.innerHTML = "";
  }, [attributes.projectId]);

  useEffect(() => {
    if (projectId) {
      fetch(`https://bdn.notice.studio/document/${encodeURIComponent(projectId)}?format=fragmented`)
        .then(async (res) => {
          const result = await res.json();

          if (res.ok) {
            const htmlElements = [result.body, `<style>${result.style}</style>`];

            for (let node of result.head) {
              // Exception for the <title>
              if (node.tagName === "title") {
                continue;
              }

              // Create the HTMLElement with browser document
              const element = document.createElement(node.tagName);

              // Assign all attributes
              for (let [key, value] of Object.entries(node.attributes)) {
                element.setAttribute(key, value);
              }

              // Assign innerHTML/innerText if necessary
              if (node.innerHTML) element.innerHTML = node.innerHTML;
              else if (node.innerText) element.innerText = node.innerText;

              htmlElements.push(element.outerHTML);
            }

            setError(null);
            editorWindow().$NTC = {};
            editorWindow().$NTC[result.id] = mandatoryFunctions(editorDocument());
            containerRef.current.innerHTML = htmlElements.join("\n");
          } else {
            setError(result.error.message);
            containerRef.current.innerHTML = "";
          }
        })
        .finally(() => {
          setLoading(false);
        });
    } else {
      setLoading(false);
      containerRef.current.innerHTML = "";
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
      <div ref={containerRef}></div>
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
