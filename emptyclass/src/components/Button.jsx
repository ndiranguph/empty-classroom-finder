import ButtonSvg from "../assets/svg/ButtonSvg";

const Button = ({classname,href,onClick,px,white}) => {
  const classes = `button relative inline-flex items-center justify-center h-11 transition-colors hover:text-color-1 ${px|| "px-7"} ${white ? "text-n-8" : "text-n-1"} ${classname || ""}`;
  
  const renderButton = () => (
    <button className="${classes}">
      <span>{children}</span>
      {ButtonSvg(white)}
    </button>
  )
  const renderLink = () => (
    <a href={href} classname="${classes}" onClick={onClick}>
      <span className={spanClasses}>{children}
      </span>
      </a>
  )
  return href ? renderLink() : renderButton();
  return renderButton();

  
};

export default Button
