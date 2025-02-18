import React from "react";
import { AppBar, Toolbar, Button, Box, Typography } from "@mui/material";
import { Link } from "react-router-dom";

const Navigation = () => {
    return (
        <AppBar position="static" sx={{ backgroundColor: "#1976d2", margin: "0px auto 20px auto" }}>
            <Toolbar>
                <Typography variant="h6" sx={{ flexGrow: 1 }}>
                    Markbook Tracker
                </Typography>

                <Box>
                    <Button color="inherit" component={Link} to="/">
                        📜 Student List
                    </Button>

                    <Button color="inherit" component={Link} to="/add-assignment">
                        ➕ Add Assignment
                    </Button>
                </Box>
            </Toolbar>
        </AppBar>
    );
};

export default Navigation;
